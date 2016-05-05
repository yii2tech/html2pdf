<?php

namespace yii2tech\tests\unit\html2pdf;

use yii2tech\tests\unit\html2pdf\data\MockConverter;

class BaseConverterTest extends TestCase
{
    public function testConvert()
    {
        $filePath = $this->ensureTestFilePath();

        $converter = new MockConverter();
        $converter->defaultOptions = [
            'option1' => 'default',
            'option2' => 'default',
        ];

        $options = [
            'option2' => 'override',
            'option3' => 'foo',
        ];
        $converter->convert(__FILE__, $filePath . '/test.pdf', $options);

        $this->assertEquals('default', $converter->lastUsedOptions['option1']);
        $this->assertEquals('override', $converter->lastUsedOptions['option2']);
        $this->assertEquals('foo', $converter->lastUsedOptions['option3']);
    }
}