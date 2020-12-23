<?php
/**
 * Created by PhpStorm.
 * @file   TemplateService.php
 * @author 李锦 <jin.li@vhall.com>
 * @date   2020/11/1 4:43 下午
 * @desc   TemplateService.php
 */

namespace Generate\Services;

class TemplateService extends AbstractService
{
    private $templateDir;

    /**
     * @return mixed
     */
    protected function __init()
    {
        $this->templateDir = BASE_PATH . "/Template/";
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
}
