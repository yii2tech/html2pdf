<?php

namespace yii2tech\tests\unit\html2pdf;

use yii\web\View;
use yii2tech\html2pdf\ConverterInterface;
use yii2tech\html2pdf\Manager;
use yii2tech\html2pdf\TempFile;
use yii2tech\tests\unit\html2pdf\data\MockConverter;

class ManagerTest extends TestCase
{
    /**
     * @return Manager manager instance.
     */
    protected function createManager()
    {
        $manager = new Manager([
            'viewPath' => __DIR__ . '/data/views',
            'layout' => 'layout',
            'converter' => [
                'class' => MockConverter::className()
            ],
        ]);
        return $manager;
    }

    // Tests :

    public function testSetupViewPath()
    {
        $manager = new Manager();

        $viewPath = '/test/file/path';
        $manager->setViewPath($viewPath);
        $this->assertEquals($viewPath, $manager->getViewPath(), 'Unable to setup view path');

        $manager = new Manager();
        $this->assertNotEmpty($manager->getViewPath(), 'Unable to get default view path');
    }

    public function testSetupView()
    {
        $manager = new Manager();

        $manager->setView([
            'title' => 'test'
        ]);
        $view = $manager->getView();
        $this->assertTrue($view instanceof View, 'Unable to setup view from array');
        $this->assertEquals('test', $view->title, 'Unable to setup view field');

        $manager = new Manager();
        $view = $manager->getView();
        $this->assertTrue($view instanceof View, 'Unable to get default view');
    }

    public function testSetupConverter()
    {
        $manager = new Manager();

        $manager->setConverter([
            'class' => MockConverter::className()
        ]);
        $converter = $manager->getConverter();
        $this->assertTrue($converter instanceof MockConverter, 'Unable to setup converter from array');

        $manager = new Manager();
        $converter = $manager->getConverter();
        $this->assertTrue($converter instanceof ConverterInterface, 'Unable to get default converter');
    }

    /**
     * @depends testSetupConverter
     */
    public function testConvert()
    {
        $manager = $this->createManager();

        $file = $manager->convert(__FILE__);
        $this->assertTrue($file instanceof TempFile);
        $this->assertTrue(file_exists($file->name));
    }

    /**
     * @depends testSetupViewPath
     * @depends testSetupView
     * @depends testConvert
     */
    public function testRender()
    {
        $manager = $this->createManager();

        $file = $manager->render('simple', ['testParam' => 'foo']);
        $this->assertTrue($file instanceof TempFile);
        $this->assertTrue(file_exists($file->name));

        $fileContent = file_get_contents($file->name);
        $this->assertContains('foo', $fileContent, 'Render fails');
        $this->assertContains('<!DOCTYPE', $fileContent, 'Layout missing');
    }
}