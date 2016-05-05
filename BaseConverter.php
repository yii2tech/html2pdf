<?php
/**
 * @link https://github.com/yii2tech
 * @copyright Copyright (c) 2015 Yii2tech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2tech\html2pdf;

use yii\base\Component;

/**
 * BaseConverter is a base class for the HTML to PDF converters.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
abstract class BaseConverter extends Component implements ConverterInterface
{
    /**
     * @var array list of the default conversion options.
     * These options will be merged with the ones specified for particular conversion.
     */
    public $defaultOptions = [];


    /**
     * @inheritdoc
     */
    public function convert($sourceFileName, $outputFileName, $options = [])
    {
        $options = array_merge($this->defaultOptions, $options);
        $this->convertInternal($sourceFileName, $outputFileName, $options);
    }

    /**
     * Converts given HTML file into PDF file.
     * @param string $sourceFileName source HTML file name.
     * @param string $outputFileName output PDF file name.
     * @param array $options conversion options.
     */
    abstract protected function convertInternal($sourceFileName, $outputFileName, $options);
}