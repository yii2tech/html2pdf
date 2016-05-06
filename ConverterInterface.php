<?php
/**
 * @link https://github.com/yii2tech
 * @copyright Copyright (c) 2015 Yii2tech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2tech\html2pdf;

/**
 * ConverterInterface defines HTML to PDF converter interface.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
interface ConverterInterface
{
    /**
     * Creates a PDF file from given HTML content.
     * @param string $html source HTML content.
     * @param string $outputFileName output PDF file name.
     * @param array $options conversion options.
     */
    public function convert($html, $outputFileName, $options = []);

    /**
     * Converts given HTML file into PDF file.
     * @param string $sourceFileName source HTML file name.
     * @param string $outputFileName output PDF file name.
     * @param array $options conversion options.
     */
    public function convertFile($sourceFileName, $outputFileName, $options = []);
}