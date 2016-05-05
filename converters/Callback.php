<?php
/**
 * @link https://github.com/yii2tech
 * @copyright Copyright (c) 2015 Yii2tech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2tech\html2pdf\converters;

use yii2tech\html2pdf\BaseConverter;

/**
 * Callback converter uses a custom PHP callback for the file convertion.
 *
 * @author Paul Klimov <pklimov@quartsoft.com>
 * @package yii2tech\html2pdf\converters
 */
class Callback extends BaseConverter
{
    /**
     * @var callable PHP callback, which should be called in order to perform actual conversion.
     * Callback should have following signature:
     *
     * ```php
     * function (string $sourceFileName, string $outputFileName, array $options) {...}
     * ```
     */
    public $callback;


    /**
     * @inheritdoc
     */
    protected function convertInternal($sourceFileName, $outputFileName, $options)
    {
        call_user_func($this->callback, $sourceFileName, $outputFileName, $options);
    }
}