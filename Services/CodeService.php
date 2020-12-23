<?php
/**
 * Created by PhpStorm.
 * @file   Code.php
 * @author 李锦 <jin.li@vhall.com>
 * @date   2020/11/1 4:57 下午
 * @desc   Code.php
 */

namespace Generate\Services;

class CodeService extends AbstractService
{

    private $templateDir;

    /**
     * @return mixed
     */
    protected function __init()
    {
        $this->templateDir = BASE_PATH . "/Code/";
    }

    /**
     * @param $template
     * @param $params
     * @return false|string|string[]
     */
    public function getCode($template, $params)
    {
        $file = $this->templateDir . $template;
        $code = file_get_contents($file);
        $target = collect(array_keys($params))->map(function ($item) {
            return "#" . $item . "#";
        })->toArray();
        return str_replace($target, array_values($params), $code);
    }
    /**
     * @param $tableName
     * @param string $type
     * @return string
     */
    public function getClassName($tableName, $type='')
    {
        $temp = collect(explode("_", $tableName))->map(function ($item) {
            return ucfirst($item);
        })->toArray();
        $temp = str_replace(ConfigService::getPrefix(), '', $temp);
        $model = join('', $temp);
        return $model . $type;
    }

}
