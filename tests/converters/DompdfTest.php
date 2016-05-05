<?php

namespace yii2tech\tests\unit\html2pdf\converters;

use yii2tech\html2pdf\converters\Dompdf;
use yii2tech\tests\unit\html2pdf\TestCase;

class DompdfTest extends TestCase
{
    protected function setUp()
    {
        if (!$this->isConverterAvailable()) {
            $this->markTestSkipped('"Dompdf" library required');
        }
    }

    /**
     * @return boolean whether converter to be tested is available.
     */
    protected function isConverterAvailable()
    {
        return class_exists('Dompdf\Dompdf', true);
    }

    // Tests :

    public function testConvert()
    {
        $converter = new Dompdf();

        $sourceFileName = dirname(__DIR__) . '/data/html/simple.html';
        $outputFileName = $this->ensureTestFilePath() . '/output.pdf';

        $converter->convert($sourceFileName, $outputFileName);

        $this->assertTrue(file_exists($outputFileName));
    }
}