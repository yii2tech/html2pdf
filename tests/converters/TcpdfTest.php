<?php

namespace yii2tech\tests\unit\html2pdf\converters;

use yii2tech\html2pdf\converters\Tcpdf;
use yii2tech\tests\unit\html2pdf\TestCase;

class TcpdfTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        if (!$this->isConverterAvailable()) {
            $this->markTestSkipped('"TCPDF" library required');
        }
    }

    /**
     * @return boolean whether converter to be tested is available.
     */
    protected function isConverterAvailable()
    {
        return class_exists('TCPDF', true);
    }

    // Tests :

    public function testConvert()
    {
        $converter = new Tcpdf();

        $sourceFileName = dirname(__DIR__) . '/data/html/simple.html';
        $outputFileName = $this->ensureTestFilePath() . '/output.pdf';

        $converter->convert($sourceFileName, $outputFileName);

        $this->assertTrue(file_exists($outputFileName));
    }
}