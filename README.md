<p align="center">
    <a href="https://github.com/yii2tech" target="_blank">
        <img src="https://avatars2.githubusercontent.com/u/12951949" height="100px">
    </a>
    <h1 align="center">HTML to PDF conversion extension for Yii2</h1>
    <br>
</p>

This extension provides basic support for HTML to PDF and PHP to PDF conversion.

For license information check the [LICENSE](LICENSE.md)-file.

[![Latest Stable Version](https://img.shields.io/packagist/v/yii2tech/html2pdf.svg)](https://packagist.org/packages/yii2tech/html2pdf)
[![Total Downloads](https://img.shields.io/packagist/dt/yii2tech/html2pdf.svg)](https://packagist.org/packages/yii2tech/html2pdf)
[![Build Status](https://travis-ci.org/yii2tech/html2pdf.svg?branch=master)](https://travis-ci.org/yii2tech/html2pdf)


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist yii2tech/html2pdf
```

or add

```json
"yii2tech/html2pdf": "*"
```

to the require section of your composer.json.

> Note: you'll have to install software for the actual HTML to PDF conversion separately, depending on the
  particular converter, you would like to use.


Usage
-----

This extension provides support for HTML to PDF and PHP to PDF conversion. It allows composition of the PDF files
from HTML and via rendering PHP templates.

Extension functionality is aggregated into `\yii2tech\html2pdf\Manager` application component.
Application configuration example:

```php
<?php

return [
    'components' => [
        'html2pdf' => [
            'class' => 'yii2tech\html2pdf\Manager',
            'viewPath' => '@app/views/pdf',
            'converter' => 'wkhtmltopdf',
        ],
    ],
    ...
];
```

For the simple conversion you can use `\yii2tech\html2pdf\Manager::convert()` and `\yii2tech\html2pdf\Manager::convertFile()` methods:

```php
<?php

$html = <<<HTML
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>
<body>
<p>Simple Content</p>
</body>
</html>
HTML;

// create PDF file from HTML content :
Yii::$app->html2pdf
    ->convert($html)
    ->saveAs('/path/to/output.pdf');

// convert HTML file to PDF file :
Yii::$app->html2pdf
    ->convertFile('/path/to/source.html')
    ->saveAs('/path/to/output.pdf');
```

The actual conversion result determined by particular converter used.
You may use `\yii2tech\html2pdf\Manager::$converter` property for the converter setup.

Several built-in converters are provided:

 - [yii2tech\html2pdf\converters\Wkhtmltopdf](src/converters/Wkhtmltopdf.php) - uses [wkhtmltopdf](http://wkhtmltopdf.org/) utility for the conversion.
 - [yii2tech\html2pdf\converters\Dompdf](src/converters/Dompdf.php) - uses [dompdf](https://github.com/dompdf/dompdf) library for the conversion.
 - [yii2tech\html2pdf\converters\Mpdf](src/converters/Mpdf.php) - uses [mpdf](https://github.com/mpdf/mpdf) library for the conversion.
 - [yii2tech\html2pdf\converters\Tcpdf](src/converters/Tcpdf.php) - uses [TCPDF](http://www.tcpdf.org) library for the conversion.
 - [yii2tech\html2pdf\converters\Callback](src/converters/Callback.php) - uses a custom PHP callback for the conversion.

**Heads up!** Most of the provided converters require additional software been installed, which is not provided by
his extension by default. You'll have to install it manually, once you decide, which converter you will use.
Please refer to the particular converter class for more details.

You may specify conversion options via second argument of the `convert()` or `convertFile()` method:

```php
<?php

Yii::$app->html2pdf
    ->convertFile('/path/to/source.html', ['pageSize' => 'A4'])
    ->saveAs('/path/to/output.pdf');
```

You may setup default conversion options at the `\yii2tech\html2pdf\Manager` level:

```php
<?php

return [
    'components' => [
        'html2pdf' => [
            'class' => 'yii2tech\html2pdf\Manager',
            'viewPath' => '@app/pdf',
            'converter' => [
                'class' => 'yii2tech\html2pdf\converters\Wkhtmltopdf',
                'defaultOptions' => [
                    'pageSize' => 'A4'
                ],
            ]
        ],
    ],
    ...
];
```

> Note: the actual list of available conversion options depends on the particular converter to be used.


## Template usage <span id="template-usage"></span>

You may create PDF files rendering PHP templates (view files), which composes HTML output.
Such files are processed as regular view files, allowing passing params and layout wrapping.
Method `\yii2tech\html2pdf\Manager::render()` used for this:

```php
<?php

Yii::$app->html2pdf
    ->render('invoice', ['user' => Yii::$app->user->identity])
    ->saveAs('/path/to/output.pdf');
```

You may use a shared layout for the templates, which can be setup via `\yii2tech\html2pdf\Manager::$layout`.

During each rendering view is working in context of `\yii2tech\html2pdf\Template` object, which can be used to adjust
layout or PDF conversion options inside view file:

```php
<?php
/* @var $this \yii\web\View */
/* @var $context \yii2tech\html2pdf\Template */
/* @var $user \app\models\User */
$context = $this->context;

$context->layout = 'layouts/payment'; // use specific layout for this template

// specify particular PDF conversion for this template:
$context->pdfOptions = [
    'pageSize' => 'A4',
    // ...
];
?>
<h1>Invoice</h1>
<p>For: <?= $user->name ?></p>
...
```
