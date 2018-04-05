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
 * @see http://wkhtmltopdf.org/
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class Wkhtmltopdf extends BaseConverter
{
    /**
     * @var string path to the 'wkhtmltopdf' command, for example: '/usr/local/bin/wkhtmltopdf'.
     * Default is 'wkhtmltopdf' assuming 'wkhtmltopdf' command is available in OS shell.
     */
    public $binPath = 'wkhtmltopdf';

    /**
     * @var array temporary html paths
     */
    protected $tempHtmlFiles = [];

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
        $command = $this->binPath;
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
    }

    /**
     * {@inheritdoc}
     */
    protected function convertInternal($html, $outputFileName, $options)
    {
        $sourceFileName = $this->createTempHtmlFile($html);

        try {
            $this->convertFileInternal($sourceFileName, $outputFileName, $options);
        } finally {
            $this->unlinkTempHtmlFiles();
        }
    }

    /**
     * Creates a temporary html file
     * @param string $html the file content
     * @return string the created file path
     */
    protected function createTempHtmlFile($html)
    {
        $tempPath = Yii::getAlias('@runtime/html2pdf');
        FileHelper::createDirectory($tempPath);

        $tempFileName = tempnam($tempPath, 'wkhtmltopdf');
        $sourceFileName = $tempFileName . '.html'; // enforce '.html' extension to avoid 'Failed loading page' error
        rename($tempFileName, $sourceFileName);
        file_put_contents($sourceFileName, $html);

        $this->tempHtmlFiles[] = $sourceFileName;

        return $sourceFileName;
    }

    /**
     * Unlink temporary html files
     */
    protected function unlinkTempHtmlFiles()
    {
        foreach ($this->tempHtmlFiles as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
        $this->tempHtmlFiles = [];
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
            // Test if the option has HTML content and creates a temporary html file
            if (in_array($normalizedName, ['header-html', 'footer-html', 'cover'])) {
                if (!is_file($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
                    $value = $this->createTempHtmlFile($value);
                }
            }
            $result[$normalizedName] = $value;
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
}
