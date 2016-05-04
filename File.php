<?php
/**
 * @link https://github.com/yii2tech
 * @copyright Copyright (c) 2015 Yii2tech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2tech\html2pdf;

use yii\base\Object;

/**
 * File
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class File extends Object
{
    /**
     * @var string the path of the file.
     * Note, this is a temporary file which will be automatically deleted on object destruction.
     */
    public $tempName;


    /**
     * Destructor.
     * Removes associated temporary file if it exists.
     */
    public function __destruct()
    {
        $this->delete();
    }

    public function copy()
    {
        ;
    }

    public function move()
    {
        ;
    }

    /**
     * Deletes associated temporary file.
     * @return boolean whether file has been deleted.
     */
    public function delete()
    {
        if (!empty($this->tempName) && file_exists($this->tempName)) {
            return unlink($this->tempName);
        }
        return false;
    }
}