<?php
/**
 * @link https://github.com/yii2tech
 * @copyright Copyright (c) 2015 Yii2tech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2tech\html2pdf\converters;

use Yii;
use yii\base\Exception;
use yii\helpers\FileHelper;
use yii\helpers\Inflector;
use yii2tech\html2pdf\BaseConverter;

/**
 * Wkhtmltopdf converts file using [wkhtmltopdf](http://wkhtmltopdf.org/) utility.
 *
 * This converter requires `wkhtmltopdf` utility installed and being available via OS shell.
 *
 * Conversion options are converted into command line options, using {@see Inflector::camel2id()}.
 * Available conversion options:
 *
 * - `pageSize`: string, page size, e.g. 'A4', 'Letter', etc.
 * - `orientation`: string, page orientation: 'Portrait' or 'Landscape'.
 * - `grayscale`: bool, whether PDF will be generated in grayscale.
 * - `cover`: string, filename or URL which holds content for cover page.
 * - `headerHtml`: string, filename or URL which holds content for pages header.
 * - `footerHtml`: string, filename or URL which holds content for pages footer.
 * - `coverContent`: string, HTML content for cover page.
 * - `headerHtmlContent`: string, header HTML content.
 * - `footerHtmlContent`: string, footer HTML content.
 *
 * Note: actual options list may vary depending on the version of 'wkhtmltopdf' you are using.
 *
 * @see http://wkhtmltopdf.org/
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class Wkhtmltopdf extends BaseConverter
{
    /**
     * @var string path to the 'wkhtmltopdf' command, for example: '/usr/local/bin/wkhtmltopdf'.
     * Since 1.0.7 Yii alias can be used here, for example: '@app/bin/wkhtmltopdf'.
     * Default is 'wkhtmltopdf' assuming 'wkhtmltopdf' command is available in OS shell.
     */
    public $binPath = 'wkhtmltopdf';

    /**
     * @var string[] list of created temporary file names.
     * @since 1.0.3
     */
    private $tmpFiles = [];


    /**
     * Destructor.
     * Ensures temporary files are deleted.
     * @since 1.0.3
     */
    public function __destruct()
    {
        $this->clearTmpFiles();
    }

    /**
     * {@inheritdoc}
     */
    public function convertFile($sourceFileName, $outputFileName, $options = [])
    {
        $options = array_merge($this->defaultOptions, $options);
        $this->convertFileInternal($sourceFileName, $outputFileName, $options);
    }

    /**
     * Converts given HTML file into PDF file.
     * @param string $sourceFileName source HTML file.
     * @param string $outputFileName output PDF file name.
     * @param array $options conversion options.
     * @throws Exception on failure.
     */
    protected function convertFileInternal($sourceFileName, $outputFileName, $options)
    {
        try {
            $command = Yii::getAlias($this->binPath);
            foreach ($this->normalizeOptions($options) as $name => $value) {
                $command .= $this->buildCommandOption($name, $value);
            }
            $command .= ' ' . escapeshellarg($sourceFileName) . ' ' . escapeshellarg($outputFileName);
            $command .= ' 2>&1';

            $outputLines = [];
            exec($command, $outputLines, $exitCode);

            if ($exitCode !== 0) {
                throw new Exception("Unable to convert file '{$sourceFileName}': " . implode("\n", $outputLines));
            }
        } catch (\Exception $e) {
            $this->clearTmpFiles();
            throw $e;
        } catch (\Throwable $e) {
            $this->clearTmpFiles();
            throw $e;
        }

        $this->clearTmpFiles();
    }

    /**
     * {@inheritdoc}
     */
    protected function convertInternal($html, $outputFileName, $options)
    {
        $sourceFileName = $this->createTmpFile($html, 'html'); // enforce '.html' extension to avoid 'Failed loading page' error
        $this->convertFileInternal($sourceFileName, $outputFileName, $options);
    }

    /**
     * Normalizes raw conversion options for the shell command composition.
     * @param array $options raw conversion options
     * @return array normalized options.
     */
    protected function normalizeOptions($options)
    {
        $result = [];

        foreach ($options as $name => $value) {
            if (is_null($value) || $value === false) {
                continue;
            }
            $normalizedName = Inflector::camel2id($name);
            $result[$normalizedName] = $value;
        }

        $fileOptions = [
            'header-html',
            'footer-html',
            'cover',
        ];
        foreach ($fileOptions as $fileOption) {
            $contentOption = $fileOption . '-content';
            if (isset($result[$contentOption])) {
                // enforce '.html' extension to avoid 'Failed loading page' error
                $result[$fileOption] = $this->createTmpFile($result[$contentOption], 'html');
                unset($result[$contentOption]);
            }
        }

        // make sure 'toc' and 'cover' options to be last, so global options will not mix with them
        uksort($result, function ($a, $b) {
            if ($a === 'toc') {
                return 1;
            }
            if ($b === 'toc') {
                return -1;
            }
            if ($a === 'cover') {
                return 1;
            }
            if ($b === 'cover') {
                return -1;
            }

            return 0;
        });

        return $result;
    }

    /**
     * Builds option for the shell command composition.
     * @param string $name option name.
     * @param mixed $value option value.
     * @return string option shell representation.
     * @since 1.0.2
     */
    protected function buildCommandOption($name, $value)
    {
        $prefix = '--';
        if (in_array($name, ['toc', 'cover'])) { // Don't add '--' in these options
            $prefix = '';
        }

        $option = ' ' . $prefix . $name;

        if ($value === true) {
            return $option;
        }

        if (is_array($value)) { // Support repeatable options
            $repeatableOptions = [];
            foreach ($value as $k => $v) {
                $repeatableOptions[] = $option . (is_string($k) ? ' ' . escapeshellarg($k) : '') . ' ' .escapeshellarg($v);
            }
            return implode(' ', $repeatableOptions);
        }

        return $option . ' ' . escapeshellarg($value);
    }

    /**
     * Returns path to directory for the temporary files storage.
     * Directory will be created if it does not yet exist.
     * @return string file path.
     * @throws \yii\base\Exception if the directory could not be created.
     * @since 1.0.3
     */
    protected function getTmpFilePath()
    {
        $tempPath = Yii::getAlias('@runtime/html2pdf');
        FileHelper::createDirectory($tempPath);
        return $tempPath;
    }

    /**
     * @param string $content file content.
     * @param null $extension file extension to be enforced.
     * @return string generated file name.
     * @throws Exception on failure.
     * @since 1.0.3
     */
    protected function createTmpFile($content, $extension = null)
    {
        $tempFileName = tempnam($this->getTmpFilePath(), 'wkhtmltopdf');
        if ($tempFileName === false) {
            throw new Exception('Unable to create temporary file.');
        }

        if ($extension !== null) {
            // sometimes enforcing of file extension, like '.html', is needed since 'wkhtmltopdf' is sensitive to it.
            $tempFileNameWithExtension = $tempFileName . '.' . $extension;
            rename($tempFileName, $tempFileNameWithExtension);
            $tempFileName = $tempFileNameWithExtension;
        }

        file_put_contents($tempFileName, $content);

        $this->tmpFiles[] = $tempFileName;
        return $tempFileName;
    }

    /**
     * Removes temporary files.
     * @since 1.0.3
     */
    protected function clearTmpFiles()
    {
        foreach ($this->tmpFiles as $tmpFile) {
            if (file_exists($tmpFile)) {
                unlink($tmpFile);
            }
        }
        $this->tmpFiles = [];
    }
}
