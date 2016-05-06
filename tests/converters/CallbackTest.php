<?php

namespace yii2tech\tests\unit\html2pdf\converters;

use yii2tech\html2pdf\converters\Callback;
use yii2tech\tests\unit\html2pdf\TestCase;

class CallbackTest extends TestCase
{
    public function testConvert()
    {
        $converter = new Callback();
        $converter->callback = function ($html, $outputFileName, $options) {
            file_put_contents($outputFileName, $html);
        };

        $sourceFileName = dirname(__DIR__) . '/data/html/simple.html';
        $outputFileName = $this->ensureTestFilePath() . '/output.pdf';

        $converter->convert(file_get_contents($sourceFileName), $outputFileName);

        $this->assertTrue(file_exists($outputFileName));
    }

    public function testConvertFile()
    {
        $converter = new Callback();
        $converter->fileCallback = function ($sourceFileName, $outputFileName, $options) {
            copy($sourceFileName, $outputFileName);
        };

        $sourceFileName = dirname(__DIR__) . '/data/html/simple.html';
        $outputFileName = $this->ensureTestFilePath() . '/output.pdf';

        $converter->convertFile($sourceFileName, $outputFileName);

        $this->assertTrue(file_exists($outputFileName));
    }
}