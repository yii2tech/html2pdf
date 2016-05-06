<?php

namespace yii2tech\tests\unit\html2pdf\data;

use yii2tech\html2pdf\BaseConverter;

class MockConverter extends BaseConverter
{
    /**
     * @var array last used convert options
     */
    public $lastUsedOptions;


    /**
     * @inheritdoc
     */
    protected function convertInternal($html, $outputFileName, $options)
    {
        $this->lastUsedOptions = $options;
        file_put_contents($outputFileName, $html);
    }
}