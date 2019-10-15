<?php
/**
 * @link https://github.com/yii2tech
 * @copyright Copyright (c) 2015 Yii2tech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2tech\html2pdf;

use Yii;
use yii\base\Component;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\di\Instance;
use yii\helpers\FileHelper;
use yii\web\View;
use yii2tech\html2pdf\converters\Callback;
use yii2tech\html2pdf\converters\Dompdf;
use yii2tech\html2pdf\converters\Mpdf;
use yii2tech\html2pdf\converters\Tcpdf;
use yii2tech\html2pdf\converters\Wkhtmltopdf;

/**
 * Manager is an application component, which provides ability for PDF composition from PHP view files
 * as well as direct HTML to PDF conversion.
 *
 * Application configuration example:
 *
 * ```php
 * return [
 *     'components' => [
 *         'html2pdf' => [
 *             'class' => 'yii2tech\html2pdf\Manager',
 *             'viewPath' => '@app/pdf',
 *             'converter' => 'wkhtmltopdf',
 *         ],
 *     ],
 *     // ...
 * ];
 * ```
 *
 * @see ConverterInterface
 * @see Template
 *
 * @property string $viewPath path ro the directory containing view files.
 * @property ConverterInterface|array|string $converter converter instance or its configuration.
 * @property View $view View instance. Note that the type of this property differs in getter and setter. See
 * {@see getView()} and {@see setView()} for details.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class Manager extends Component
{
    /**
     * @var string|bool layout view name. This is the layout used to render HTML source.
     * The property can take the following values:
     *
     * - a relative view name: a view file relative to {@see viewPath}, e.g., 'layouts/main'.
     * - a path alias: an absolute view file path specified as a path alias, e.g., '@app/pdf/layout'.
     * - a bool false: the layout is disabled.
     */
    public $layout = 'layouts/main';

    /**
     * @var string path to the directory containing view files.
     */
    private $_viewPath;
    /**
     * @var \yii\base\View|array view instance or its array configuration.
     */
    private $_view = [];
    /**
     * @var ConverterInterface|array|string converter instance or its configuration.
     */
    private $_converter = 'wkhtmltopdf';


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
            throw new InvalidConfigException('"' . get_class($this) . '::$view" should be either object or configuration array, "' . gettype($view) . '" given.');
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
                        'class' => Wkhtmltopdf::className()
                    ];
                    break;
                case 'mpdf':
                    $config = [
                        'class' => Mpdf::className()
                    ];
                    break;
                case 'tcpdf':
                    $config = [
                        'class' => Tcpdf::className()
                    ];
                    break;
                case 'dompdf':
                    $config = [
                        'class' => Dompdf::className()
                    ];
                    break;
            }
        } elseif (is_array($config)) {
            if (!array_key_exists('class', $config)) {
                $config['class'] = Callback::className();
            }
        }

        return Instance::ensure($config, 'yii2tech\html2pdf\ConverterInterface');
    }

    /**
     * Renders the specified view with optional parameters and layout and converts rendering result into the PDF file.
     * @param string $view the view name or the path alias of the view file.
     * @param array $params the parameters (name-value pairs) that will be extracted and made available in the view file.
     * @return TempFile converted PDF file representation.
     */
    public function render($view, $params = [])
    {
        $template = new Template([
            'view' => $this->getView(),
            'viewPath' => $this->getViewPath(),
            'viewName' => $view,
            'layout' => $this->layout,
        ]);
        $htmlContent = $template->render($params);

        return $this->convert($htmlContent, $template->pdfOptions);
    }

    /**
     * Converts HTML content into PDF file.
     * @param string $html source HTML content.
     * @param array $options conversion options.
     * @return TempFile converted PDF file representation.
     * @throws Exception on failure.
     */
    public function convert($html, $options = [])
    {
        $outputFileName = $this->generateTempFileName('pdf');
        $this->getConverter()->convert($html, $outputFileName, $options);
        if (!file_exists($outputFileName)) {
            throw new Exception('HTML to PDF conversion failed: no output file created.');
        }
        return new TempFile(['name' => $outputFileName]);
    }

    /**
     * Converts HTML file into PDF file.
     * @param string $fileName source file name.
     * @param array $options conversion options.
     * @return TempFile converted PDF file representation.
     * @throws Exception on failure.
     */
    public function convertFile($fileName, $options = [])
    {
        $outputFileName = $this->generateTempFileName('pdf');
        $this->getConverter()->convertFile($fileName, $outputFileName, $options);
        if (!file_exists($outputFileName)) {
            throw new Exception('HTML to PDF conversion failed: no output file created.');
        }
        return new TempFile(['name' => $outputFileName]);
    }

    /**
     * Generates temporary file name.
     * @param string $extension file extension.
     * @return string full path to temp file.
     */
    protected function generateTempFileName($extension = 'tmp')
    {
        $tempPath = Yii::getAlias('@runtime/html2pdf');
        FileHelper::createDirectory($tempPath);

        do {
            $fileName = $tempPath . DIRECTORY_SEPARATOR . uniqid('html2pdf', true) . '.' . $extension;
        } while (file_exists($fileName));

        return $fileName;
    }
}