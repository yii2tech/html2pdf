<?php

namespace yii2tech\tests\unit\html2pdf;

use yii\web\View;
use yii2tech\html2pdf\Manager;

class ManagerTest extends TestCase
{
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
}