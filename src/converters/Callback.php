<?php
/**
 * @link https://github.com/yii2tech
 * @copyright Copyright (c) 2015 Yii2tech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2tech\html2pdf\converters;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\FileHelper;
use yii2tech\html2pdf\BaseConverter;

/**
 * Callback converter uses a custom PHP callback for the file conversion.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class Callback extends BaseConverter
{
    /**
     * @var callable PHP callback, which should be called in order to perform conversion of HTML content into a PDF file.
     * Callback should have following signature:
     *
     * ```php
     * function (string $htmlContent, string $outputFileName, array $options) {...}
     * ```
     *
     * This field can be omitted in case {@see fileCallback} is set.
     */
    public $callback;
    /**
     * @var callable PHP callback, which should be called in order to perform conversion of HTML file into a PDF file.
     * Callback should have following signature:
     *
     * ```php
     * function (string $sourceFileName, string $outputFileName, array $options) {...}
     * ```
     *
     * This field can be omitted in case {@see callback} is set.
     */
    public $fileCallback;


    /**
     * {@inheritdoc}
     */
    public function convertFile($sourceFileName, $outputFileName, $options = [])
    {
        if ($this->fileCallback === null) {
            parent::convertFile($sourceFileName, $outputFileName, $options);
        } else {
            $options = array_merge($this->defaultOptions, $options);
            call_user_func($this->fileCallback, $sourceFileName, $outputFileName, $options);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function convertInternal($html, $outputFileName, $options)
    {
        if ($this->callback === null) {
            if ($this->fileCallback === null) {
                throw new InvalidConfigException("Either 'callback' or 'fileCallback' must be set.");
            }

            $tempPath = Yii::getAlias('@runtime/html2pdf');
            FileHelper::createDirectory($tempPath);

            $sourceFileName = tempnam($tempPath, 'wkhtmltopdf');
            file_put_contents($sourceFileName, $html);

            try {
                $this->convertFile($sourceFileName, $outputFileName, $options);
            } catch (\Exception $e) {
                unlink($sourceFileName);
                throw $e;
            }

            unlink($sourceFileName);
        } else {
            call_user_func($this->callback, $html, $outputFileName, $options);
        }
    }
}