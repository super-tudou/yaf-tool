<?php
/**
 * Created by PhpStorm.
 * @file   AbstractBase.php
 * @author 李锦 <jin.li@vhall.com>
 * @date   2020/10/30 4:09 下午
 * @desc   AbstractBase.php
 */

namespace Generate\Common;

abstract class AbstractBase
{

    protected static $obj = [];
    protected $params = [];


    /**
     * AbstractBase constructor.
     * @param array $params
     */
    private function __construct($params = [])
    {
        $this->params = $params;
        $this->__init();
    }

    /**
     * @return mixed
     */
    abstract protected function __init();


    /**
     * @return $this|mixed
     */

    /**
     * @param $params
     * @return static
     * @return mixed
     */
    public static function getInstance($params = '')
    {
        $key = static::class;
        if (!isset(self::$obj[$key]) || !(self::$obj instanceof static)) {
            self::$obj[$key] = new static($params);
        }
        return self::$obj[$key];
    }
}
