<?php

/**
 * 编号生成器
 */

namespace MeaPHP\Core\Tools;

class BuildID
{

    private static $obj = null;

    private $unixTime;

    //阻止外部克隆书库工具类
    private function __clone()
    {
    }

    //私有化构造方法初始化，禁止外部使用
    private function __construct()
    {

        $this->timeUnixNum();
    }
    //内部产生静态对象
    public static function start()
    {
        // var_dump($dbkey);
        if (!self::$obj instanceof self) {
            //如果不存在，创建保存
            self::$obj = new self();
        }
        return self::$obj;
    }

    //生成毫秒级时间戳
    private function timeUnixNum()
    {

        list($s1, $s2)  = explode(' ', microtime());
        $this->unixTime = (float) sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
    }

    //返回加工好的而编号

    public function getID($prefix1 = null, $prefix2 = null, $prefix3 = null)
    {
        if (!$prefix1) {
            return 'error:缺少前缀1；编号规则：前缀1+[前缀2]+[前缀3]+毫秒级时间戳;建议至少有2个前缀';
        } elseif ($prefix1 && !$prefix2) {
            return $prefix1  . $this->unixTime;
        } elseif ($prefix1 && $prefix2 && !$prefix3) {
            return $prefix1 . $prefix2 . $this->unixTime;
        } elseif ($prefix1 && $prefix2 && $prefix3) {
            return $prefix1 . $prefix2 . $prefix3 . $this->unixTime;
        }
    }
}
