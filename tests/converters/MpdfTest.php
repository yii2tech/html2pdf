<?php

namespace yii2tech\tests\unit\html2pdf\converters;

use yii2tech\html2pdf\converters\Mpdf;
use yii2tech\tests\unit\html2pdf\TestCase;

class MpdfTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        if (!$this->isConverterAvailable()) {
            $this->markTestSkipped('"Mpdf" library required');
        }
    }

    /**
     * @return boolean whether converter to be tested is available.
     */
    protected function isConverterAvailable()
    {
        return class_exists('mPDF', true);
    }

    // Tests :

    public function testConvert()
    {
        $converter = new Mpdf();

        $sourceFileName = dirname(__DIR__) . '/data/html/simple.html';
        $outputFileName = $this->ensureTestFilePath() . '/output.pdf';

        $converter->convertFile($sourceFileName, $outputFileName);

        $this->assertTrue(file_exists($outputFileName));
    }
}