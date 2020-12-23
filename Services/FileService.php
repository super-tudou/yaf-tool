<?php
/**
 * Created by PhpStorm.
 * @file   FileService.php
 * @author 李锦 <jin.li@vhall.com>
 * @date   2020/11/1 4:53 下午
 * @desc   FileService.php
 */

namespace Generate\Services;


class FileService extends AbstractService
{

    private $path;

    /**
     * @return mixed
     */
    protected function __init()
    {
        $this->path = APP_PATH;
    }

    public function saveCode($file, $code)
    {
       echo  $file = $this->path . $file;
        return file_put_contents($file, $code);
    }

}
