<?php
/**
 * @link https://github.com/yii2tech
 * @copyright Copyright (c) 2015 Yii2tech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2tech\html2pdf\converters;

use yii\base\Exception;
use yii\helpers\Inflector;
use yii2tech\html2pdf\BaseConverter;

/**
 * Wkhtmltopdf converts file using [wkhtmltopdf](http://wkhtmltopdf.org/) utility.
 *
 * This converter requires `wkhtmltopdf` utility installed and being available via OS shell.
 *
 * @see http://wkhtmltopdf.org/
 *
 * @author Paul Klimov <pklimov@quartsoft.com>
 * @package yii2tech\html2pdf\converters
 */
class Wkhtmltopdf extends BaseConverter
{
    /**
     * @var string path to the 'wkhtmltopdf' command, for example: '/usr/bin/wkhtmltopdf'.
     * Default is 'wkhtmltopdf' assuming 'wkhtmltopdf' command is available in OS shell.
     */
    public $binPath = 'wkhtmltopdf';


    /**
     * @inheritdoc
     */
    protected function convertInternal($sourceFileName, $outputFileName, $options)
    {
        $command = $this->binPath;
        foreach ($this->normalizeOptions($options) as $name => $value) {
            $command .= " --{$name} {$value}";
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
     * Normalizes raw conversion options for the shell command composition.
     * @param array $options raw conversion options
     * @return array normalized options.
     */
    protected function normalizeOptions($options)
    {
        $result = [];
        foreach ($options as $name => $value) {
            $normalizedName = Inflector::camel2id($name);
            $result[$normalizedName] = $value;
        }
        return $result;
    }
}