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
    protected function convertInternal($sourceFileName, $outputFileName, $options)
    {
        $this->lastUsedOptions = $options;
        copy($sourceFileName, $outputFileName);
    }
}