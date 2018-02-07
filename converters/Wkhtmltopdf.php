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
     * @var array list of command options aliases.
     */
    protected $optionAlias = [
        'd' => 'dpi',
        'H' => 'extended-help',
        'g' => 'grayscale',
        'h' => 'help',
        'l' => 'lowquality',
        'B' => 'margin-bottom',
        'L' => 'margin-left',
        'R' => 'margin-right',
        'T' => 'margin-top',
        'O' => 'orientation',
        's' => 'page-size',
        'q' => 'quiet',
        'V' => 'version',
        'n' => 'disable-javascript',
        'p' => 'proxy',
    ];

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
        $tempPath = Yii::getAlias('@runtime/html2pdf');
        FileHelper::createDirectory($tempPath);

        $tempFileName = tempnam($tempPath, 'wkhtmltopdf');
        $sourceFileName = $tempFileName . '.html'; // enforce '.html' extension to avoid 'Failed loading page' error
        rename($tempFileName, $sourceFileName);
        file_put_contents($sourceFileName, $html);

        try {
            $this->convertFileInternal($sourceFileName, $outputFileName, $options);
        } catch (\Exception $e) {
            unlink($sourceFileName);
            throw $e;
        }

        unlink($sourceFileName);
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
            if (isset($this->optionAlias[$name])) {
                $name = $this->optionAlias[$name];
            }
            $normalizedName = Inflector::camel2id($name);
            $result[$normalizedName] = $value;
        }
        return $result;
    }

    /**
     * Build option for the shell command composition
     * @param string $name option name
     * @param mixed $value option value
     * @return string option name-value pair
     */
    protected function buildCommandOption($name, $value)
    {
        $prefix = '--';
        if (in_array($name, ['toc', 'cover'])) { // Don't add '--' in these options
            $prefix = '';
        }

        $option = " {$prefix}{$name}";

        if ($value !== true) {
            $option .= ' ' . escapeshellarg($value);
        }
        return $option;
    }
}
