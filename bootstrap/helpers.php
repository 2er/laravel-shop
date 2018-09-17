<?php
/**
 * Created by PhpStorm.
 * User: wuchuanchuan
 * Date: 2018/9/17
 * Time: 上午10:25
 */

function route_class()
{
    return str_replace('.', '-', Route::currentRouteName());
}