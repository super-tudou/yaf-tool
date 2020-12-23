<?php
/**
 * Created by PhpStorm.
 * @file   helpers.php
 * @author 李锦 <jin.li@vhall.com>
 * @date   2020/11/10 2:03 下午
 * @desc   helpers.php
 */
if (! function_exists('collect')) {
    /**
     * Create a collection from the given value.
     *
     * @param  mixed  $value
     * @return \Illuminate\Support\Collection
     */
    function collect($value = null)
    {
        return new \Illuminate\Support\Collection($value);
    }
}
