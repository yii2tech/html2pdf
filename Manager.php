<?php
/**
 * @link https://github.com/yii2tech
 * @copyright Copyright (c) 2015 Yii2tech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2tech\html2pdf;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\base\ViewContextInterface;
use yii\di\Instance;
use yii\helpers\FileHelper;
use yii\web\View;

/**
 * Manager
 *
 * @property string $viewPath path ro the directory containing view files.
 * @property ConverterInterface|array|string $converter converter instance or its configuration.
 * @property View $view View instance. Note that the type of this property differs in getter and setter. See
 * [[getView()]] and [[setView()]] for details.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class Manager extends Component implements ViewContextInterface
{
    /**
     * @var string path ro the directory containing view files.
     */
    private $_viewPath;
    /**
     * @var \yii\base\View|array view instance or its array configuration.
     */
    private $_view;
    /**
     * @var ConverterInterface|array|string converter instance or its configuration.
     */
    private $_converter = 'wkhtmltopdf';

    /**
     * @var string|boolean layout view name. This is the layout used to render HTML source.
     * The property can take the following values:
     *
     * - a relative view name: a view file relative to [[viewPath]], e.g., 'layouts/main'.
     * - a path alias: an absolute view file path specified as a path alias, e.g., '@app/pdf/layout'.
     * - a boolean false: the layout is disabled.
     */
    public $layout = 'layouts/main';


    /**
     * Returns the directory containing view files.
     * @return string the view path that may be prefixed to a relative view name.
     */
    public function getViewPath()
    {
        if ($this->_viewPath === null) {
            $this->setViewPath('@app/pdf');
        }
        return $this->_viewPath;
    }

    /**
     * Sets the directory that contains the view files.
     * @param string $path the root directory of view files.
     */
    public function setViewPath($path)
    {
        $this->_viewPath = Yii::getAlias($path);
    }

    /**
     * @param array|View $view view instance or its array configuration that will be used to
     * render message bodies.
     * @throws InvalidConfigException on invalid argument.
     */
    public function setView($view)
    {
        if (!is_array($view) && !is_object($view)) {
            throw new InvalidConfigException('"' . get_class($this) . '::view" should be either object or configuration array, "' . gettype($view) . '" given.');
        }
        $this->_view = $view;
    }

    /**
     * @return View view instance.
     */
    public function getView()
    {
        if (!is_object($this->_view)) {
            $this->_view = $this->createView($this->_view);
        }
        return $this->_view;
    }

    /**
     * @return ConverterInterface converter instance
     */
    public function getConverter()
    {
        if (!is_object($this->_converter)) {
            $this->_converter = $this->createConverter($this->_converter);
        }
        return $this->_converter;
    }

    /**
     * @param ConverterInterface|array|string $converter
     */
    public function setConverter($converter)
    {
        $this->_converter = $converter;
    }

    /**
     * Creates view instance from given configuration.
     * @param array $config view configuration.
     * @return View view instance.
     */
    protected function createView(array $config)
    {
        if (!array_key_exists('class', $config)) {
            $config['class'] = View::className();
        }
        return Yii::createObject($config);
    }

    /**
     * Creates converter instance from given configuration.
     * @param mixed $config converter configuration.
     * @return View view instance.
     */
    protected function createConverter($config)
    {
        if (is_string($config)) {
            switch (strtolower($config)) {
                case 'wkhtmltopdf':
                    $config = [
                        WkhtmltopdfConverter::className()
                    ];
                    break;
                case 'mpdf':
                    $config = [
                        MpdfConverter::className()
                    ];
                    break;
                case 'tcpdf':
                    $config = [
                        TcpdfConverter::className()
                    ];
                    break;
            }
        } elseif (is_array($config)) {
            if (!array_key_exists('class', $config)) {
                $config['class'] = CallbackConverter::className();
            }
        }

        return Instance::ensure($config, 'yii2tech\html2pdf\ConverterInterface');
    }

    /**
     * Renders the specified view with optional parameters and layout and converts rendering result into the PDF file.
     * @param string $view the view name or the path alias of the view file.
     * @param array $params the parameters (name-value pairs) that will be extracted and made available in the view file.
     * @param array $options conversion options
     * @return File converted PDF file representation.
     */
    public function render($view, $params, $options = [])
    {
        $htmlContent = $this->renderHtml($view, $params);
        $fileName = $this->generateTempFileName();
        file_put_contents($fileName, $htmlContent);
        return $this->convert($fileName, $options);
    }

    /**
     * Converts HTML file into PDF file.
     * @param string $fileName source file name.
     * @param array $options conversion options.
     * @return File converted PDF file representation.
     */
    public function convert($fileName, $options = [])
    {
        $outputFileName = $this->generateTempFileName();
        $this->getConverter()->convert($fileName, $outputFileName, $options);
        return new File(['tempName' => $outputFileName]);
    }

    /**
     * Renders the specified view with optional parameters and layout.
     * The view will be rendered using the [[view]] component.
     * @param string $view the view name or the path alias of the view file.
     * @param array $params the parameters (name-value pairs) that will be extracted and made available in the view file.
     * @return string the rendering result.
     */
    protected function renderHtml($view, $params)
    {
        $output = $this->getView()->render($view, $params, $this);
        if ($this->layout !== false) {
            return $this->getView()->render($this->layout, ['content' => $output], $this);
        } else {
            return $output;
        }
    }

    /**
     * Generates temporary file name.
     * @param string $extension file extension.
     * @return string full path to temp file.
     */
    protected function generateTempFileName($extension = 'tmp')
    {
        $tempPath = Yii::getAlias('@runtime/html2pdf');
        if (!file_exists($tempPath)) {
            FileHelper::createDirectory($tempPath);
        }

        do {
            $fileName = $tempPath . DIRECTORY_SEPARATOR . uniqid('html2pdf', true) . '.' . $extension;
        } while (!file_exists($fileName));

        return $fileName;
    }
}