<?php

namespace yii2tech\tests\unit\html2pdf\converters;

use yii2tech\html2pdf\converters\Wkhtmltopdf;
use yii2tech\tests\unit\html2pdf\TestCase;

class WkhtmltopdfTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        if (!$this->isConverterAvailable()) {
            $this->markTestSkipped('Shell command "wkhtmltopdf" is unavailable');
        }
    }

    /**
     * @return bool whether converter to be tested is available.
     */
    protected function isConverterAvailable()
    {
        $lines = [];
        exec('wkhtmltopdf --version 2>&1', $lines, $exitCode);
        return $exitCode === 0;
    }

    // Tests :

    public function testConvertFile()
    {
        $converter = new Wkhtmltopdf();

        $sourceFileName = dirname(__DIR__) . '/data/html/simple.html';
        $outputFileName = $this->ensureTestFilePath() . '/output.pdf';

        $converter->convertFile($sourceFileName, $outputFileName);

        $this->assertTrue(file_exists($outputFileName));
    }

    public function testConvert()
    {
        $converter = new Wkhtmltopdf();

        $sourceFileName = dirname(__DIR__) . '/data/html/simple.html';
        $outputFileName = $this->ensureTestFilePath() . '/output.pdf';

        $converter->convert(file_get_contents($sourceFileName), $outputFileName);

        $this->assertTrue(file_exists($outputFileName));
    }
}