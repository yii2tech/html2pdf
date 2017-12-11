<?php
/**
 * @link https://github.com/yii2tech
 * @copyright Copyright (c) 2015 Yii2tech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2tech\html2pdf\converters;

use Yii;
use yii\helpers\ArrayHelper;
use yii2tech\html2pdf\BaseConverter;

/**
 * Tcpdf converts file using [TCPDF](http://www.tcpdf.org) library.
 *
 * This converter requires `TCPDF` library to be installed. This can be done via composer:
 *
 * ```
 * composer require --prefer-dist tecnickcom/tcpdf
 * ```
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class Tcpdf extends BaseConverter
{
    /**
     * {@inheritdoc}
     */
    protected function convertInternal($html, $outputFileName, $options)
    {
        $charset = ArrayHelper::remove($options, 'charset', Yii::$app->charset);
        $pageSize = ArrayHelper::remove($options, 'pageSize', 'A4');
        $orientation = ucfirst(ArrayHelper::remove($options, 'orientation', 'P'));
        $unit = ArrayHelper::remove($options, 'unit', 'mm');

        $pdf = new \TCPDF($orientation, $unit, $pageSize, true, $charset, false);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        foreach ($options as $name => $value) {
            $setter = 'Set' . $name;
            if (method_exists($pdf, $setter)) {
                $pdf->$setter($value);
            } else {
                $pdf->$name = $value;
            }
        }

        // add a page
        $pdf->AddPage();

        $pdf->WriteHTML($html);
        $pdf->Output($outputFileName, 'F');
    }
}