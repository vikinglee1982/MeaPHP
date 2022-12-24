<?php

namespace singleApi;

class AutoLoad
{
    private static $DBobj = null;
    //阻止外部克隆书库工具类
    private function __clone()
    {
    }

    //构造方法初始化，属性赋值，准备连接
    private function __construct($dbkey)
    {
    }


    //内部产生静态对象
    public static function start($dbkey)
    {
        // var_dump($dbkey);
        if (!self::$DBobj instanceof self) {
            //如果不存在，创建保存
            self::$DBobj = new self($dbkey);
        }
        return self::$DBobj;
    }
}


AutoLoad::start();
