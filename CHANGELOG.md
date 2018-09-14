Yii 2 HTML to PDF conversion extension Change Log
=================================================

1.0.4 under development
-----------------------

- Bug #12: Fixed inability to setup 'tempDir' and 'fontDir' for `Mpdf` converter (klimov-paul)
- Enh #15: Added method `TempFile::getContent()` (mludvik)

1.0.3, April 9, 2018
--------------------

- Enh #6: Options 'coverContent', 'headerHtmlContent' and 'footerHtmlContent' added to `Wkhtmltopdf` (berosoboy, klimov-paul)
- Enh #8: 'wkhtmltopdf' command composition improved ensuring options 'cover' and 'toc' do not utilize global ones (klimov-paul)


1.0.2, February 13, 2018
------------------------

- Enh #3: Added support for mPDF version >= 7.0 (klimov-paul)
- Enh #5: 'wkhtmltopdf' command composition improved adding support for boolean and array options (berosoboy)


1.0.1, November 3, 2017
-----------------------

- Bug: Usage of deprecated `yii\base\Object` changed to `yii\base\BaseObject` allowing compatibility with PHP 7.2 (klimov-paul)


1.0.0, May 19, 2016
-------------------

- Initial release.
