<?php

namespace yii2tech\tests\unit\html2pdf\converters;

use yii2tech\html2pdf\converters\Wkhtmltopdf;
use yii2tech\tests\unit\html2pdf\TestCase;

/**
 * @group wkhtmltopdf
 */
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

    /**
     * Data provider for [[testNormalizeOptions()]].
     * @return array test data
     */
    public function dataProviderNormalizeOptions()
    {
        return [
            [
                [
                    'pageSize' => 'A4'
                ],
                [
                    'page-size' => 'A4'
                ],
            ],
            [
                [
                    'toc' => '/path/to/table-of-content',
                    'cover' => '/path/to/cover',
                    'pageSize' => 'A4',
                ],
                [
                    'page-size' => 'A4',
                    'cover' => '/path/to/cover',
                    'toc' => '/path/to/table-of-content',
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderNormalizeOptions
     *
     * @param array $rawOptions
     * @param array $expectedNormalizedOptions
     */
    public function testNormalizeOptions(array $rawOptions, array $expectedNormalizedOptions)
    {
        $converter = new Wkhtmltopdf();
        $normalizedOptions = $this->invoke($converter, 'normalizeOptions', [$rawOptions]);
        $this->assertSame($expectedNormalizedOptions, $normalizedOptions);
    }

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

    /**
     * @depends testConvertFile
     */
    public function testContentOptions()
    {
        $converter = new Wkhtmltopdf();

        $sourceFileName = dirname(__DIR__) . '/data/html/simple.html';
        $outputFileName = $this->ensureTestFilePath() . '/output.pdf';

        $converter->convertFile($sourceFileName, $outputFileName, [
            'coverContent' => 'cover content',
        ]);

        $this->assertTrue(file_exists($outputFileName));
    }
}