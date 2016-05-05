<?php

namespace yii2tech\tests\unit\html2pdf;

use yii\base\View;
use yii2tech\html2pdf\Template;

class TemplateTest extends TestCase
{
    /**
     * @param array $config template configuration
     * @return Template template instance
     */
    protected function createTemplate($config = [])
    {
        $config['viewPath'] = __DIR__ . '/data/views';
        $config['view'] = new View();
        return new Template($config);
    }

    // Tests :

    public function testRender()
    {
        $template = $this->createTemplate([
            'viewName' => 'simple',
            'layout' => 'layout',
        ]);
        $html = $template->render(['testParam' => 'test value']);

        $this->assertContains('<p>Simple</p>', $html, 'View not rendered');
        $this->assertContains('test value', $html, 'Param not passed');
        $this->assertContains('<!DOCTYPE', $html, 'No layout is rendered');

        $template = $this->createTemplate([
            'viewName' => 'simple',
            'layout' => false,
        ]);
        $html = $template->render(['testParam' => 'test value']);
        $this->assertNotContains('<!DOCTYPE', $html, 'Unable layout disabling');
    }
}