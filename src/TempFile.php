<?php
/**
 * @link https://github.com/yii2tech
 * @copyright Copyright (c) 2015 Yii2tech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2tech\html2pdf;

use Yii;
use yii\base\BaseObject;
use yii\helpers\FileHelper;

/**
 * TempFile represents HTML to PDF conversion result file.
 * An associated file will be automatically deleted on the instance destruction.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class TempFile extends BaseObject
{
    /**
     * @var string the path of the file.
     * Note, this is a temporary file which will be automatically deleted on object destruction.
     */
    public $name;


    /**
     * Destructor.
     * Removes associated temporary file if it exists.
     */
    public function __destruct()
    {
        $this->delete();
    }

    /**
     * Copies this file into another location.
     * @param string $destinationFileName destination file name (may content path alias).
     * @return bool whether operation was successful.
     */
    public function copy($destinationFileName)
    {
        $destinationFileName = $this->prepareDestinationFileName($destinationFileName);
        return copy($this->name, $destinationFileName);
    }

    /**
     * Moves this file into another location.
     * @param string $destinationFileName destination file name (may content path alias).
     * @return bool whether operation was successful.
     */
    public function move($destinationFileName)
    {
        $destinationFileName = $this->prepareDestinationFileName($destinationFileName);
        $result = rename($this->name, $destinationFileName);
        $this->name = null;
        return $result;
    }

    /**
     * Saves this file.
     * @param string $file destination file name (may content path alias).
     * @param bool $deleteTempFile whether to delete associated temp file or not.
     * @return bool whether operation was successful.
     */
    public function saveAs($file, $deleteTempFile = true)
    {
        if ($deleteTempFile) {
            return $this->move($file);
        }
        return $this->copy($file);
    }

    /**
     * Prepares raw destination file name for the file copy/move operation:
     * resolves path alias and creates missing directories.
     * @param string $destinationFileName destination file name
     * @return string real destination file name
     */
    protected function prepareDestinationFileName($destinationFileName)
    {
        $destinationFileName = Yii::getAlias($destinationFileName);
        $destinationPath = dirname($destinationFileName);
        FileHelper::createDirectory($destinationPath);
        return $destinationFileName;
    }

    /**
     * Deletes associated temporary file.
     * @return bool whether file has been deleted.
     */
    public function delete()
    {
        if (!empty($this->name) && file_exists($this->name)) {
            $result = FileHelper::unlink($this->name);
            $this->name = null;
            return $result;
        }
        return false;
    }

    /**
     * Gets content of this file.
     * @return string file content.
     * @since 1.0.4
     */
    public function getContent()
    {
        return file_get_contents($this->name);
    }

    /**
     * Prepares response for sending a file to the browser.
     * Note: this method works only while running web application.
     * @param string $name the file name shown to the user. If null, it will be determined from {@see name}.
     * @param array $options additional options for sending the file. See {@see \yii\web\Response::sendFile()} for more details.
     * @return \yii\web\Response application response instance.
     */
    public function send($name = null, $options = [])
    {
        return Yii::$app->getResponse()->sendFile($this->name, $name, $options);
    }
}
