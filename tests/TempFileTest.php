<?php

namespace yii2tech\tests\unit\html2pdf;

use yii2tech\html2pdf\TempFile;

class TempFileTest extends TestCase
{
    public function testDelete()
    {
        $filePath = $this->ensureTestFilePath();
        $fileName = $filePath . '/test.txt';
        file_put_contents($fileName, 'test content');

        $file = new TempFile();
        $file->name = $fileName;

        $this->assertTrue($file->delete());
        $this->assertFalse(file_exists($fileName));
    }

    /**
     * @depends testDelete
     */
    public function testAutoDelete()
    {
        $filePath = $this->ensureTestFilePath();
        $fileName = $filePath . '/test.txt';
        file_put_contents($fileName, 'test content');

        $file = new TempFile();
        $file->name = $fileName;

        unset($file);

        $this->assertFalse(file_exists($fileName));
    }

    public function testCopy()
    {
        $filePath = $this->ensureTestFilePath();
        $fileName = $filePath . '/test.txt';
        file_put_contents($fileName, 'test content');

        $file = new TempFile();
        $file->name = $fileName;

        $destinationFileName = $filePath . '/destination.txt';
        $file->copy($destinationFileName);

        $this->assertTrue(file_exists($destinationFileName));
        $this->assertTrue(file_exists($fileName));
    }

    public function testMove()
    {
        $filePath = $this->ensureTestFilePath();
        $fileName = $filePath . '/test.txt';
        file_put_contents($fileName, 'test content');

        $file = new TempFile();
        $file->name = $fileName;

        $destinationFileName = $filePath . '/destination.txt';
        $file->move($destinationFileName);

        $this->assertTrue(file_exists($destinationFileName));
        $this->assertFalse(file_exists($fileName));
    }
}