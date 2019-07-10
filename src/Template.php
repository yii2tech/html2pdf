<?php
/**
 * @link https://github.com/yii2tech
 * @copyright Copyright (c) 2015 Yii2tech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2tech\html2pdf;

use yii\base\BaseObject;
use yii\base\ViewContextInterface;

/**
 * Template represents particular PDF file view template.
 * Its instance will be available inside view file via {@see \yii\base\View::$context}.
 *
 * @see Manager
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class Template extends BaseObject implements ViewContextInterface
{
    /**
     * @var string path ro the directory containing view files.
     */
    public $viewPath;
    /**
     * @var string the view name for his template.
     */
    public $viewName;
    /**
     * @var string|bool layout view name. This is the layout used to render HTML source.
     * The property can take the following values:
     *
     * - a relative view name: a view file relative to {@see viewPath}, e.g., 'layouts/main'.
     * - a path alias: an absolute view file path specified as a path alias, e.g., '@app/pdf/layout'.
     * - a bool false: the layout is disabled.
     */
    public $layout;
    /**
     * @var \yii\base\View view instance, which should be used for rendering.
     */
    public $view;
    /**
     * @var array list of HTML to PDF conversion options, which should be applied while converting
     * template rendering result into PDF.
     */
    public $pdfOptions = [];


    /**
     * {@inheritdoc}
     */
    public function getViewPath()
    {
        return $this->viewPath;
    }

    /**
     * Renders this template.
     * @param array $params the parameters (name-value pairs) that will be extracted and made available in the view file.
     * @return string the rendering result.
     */
    public function render($params = [])
    {
        $output = $this->view->render($this->viewName, $params, $this);
        if ($this->layout !== false) {
            return $this->view->render($this->layout, ['content' => $output], $this);
        } else {
            return $output;
        }
    }
}