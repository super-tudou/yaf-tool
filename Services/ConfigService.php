<?php
/**
 * Created by PhpStorm.
 * @file   ConfigService.php
 * @author 李锦 <jin.li@vhall.com>
 * @date   2020/11/10 10:51 上午
 * @desc   ConfigService.php
 */

namespace Generate\Services;


use Symfony\Component\Yaml\Yaml;

class ConfigService extends AbstractService
{
    private $config = [];

    /**
     * @return mixed
     */
    protected function __init()
    {
        $this->config = Yaml::parseFile(BASE_PATH . '/Config/config.yml');
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    public static function getPrefix()
    {
        return ['Rbac'];
    }

    public static function getModuleName()
    {
        return 'modules/V3Users/';
    }
}

