<?php

/**
 * 编号生成器
 */

namespace MeaPHP\Core\Tools;

class MID
{

    private static $obj = null;

    private $unixTime;

    public $res = array(
        // 'status' => 'error',
        // //只有2种状态 ok/error
        // 'data' => null,
        // //正确：返回数据
        // 'msg' => null,
        //错误：返回错误原因
    );



    //阻止外部克隆书库工具类
    private function __clone()
    {
    }

    //私有化构造方法初始化，禁止外部使用
    private function __construct()
    {

        $this->res = array();
        $this->timeUnixNum();
    }
    //内部产生静态对象
    public static function active()
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
    //使用建议 $prefix1 = 订单类别 $prefix2 = uid
    public function create($prefix1 = null, $prefix2 = null, $prefix3 = null)
    {
        if (!$prefix1) {
            $this->res['status'] = 'error';
            $this->res['msg'] = '至少有一个编号前缀';
            // return 'error:至少有一个编号前缀';
        } elseif ($prefix1 && !$prefix2) {
            $this->res['status'] = 'ok';
            $this->res['data'] = $prefix1  . $this->unixTime;
            // return $prefix1  . $this->unixTime;
        } elseif ($prefix1 && $prefix2 && !$prefix3) {
            $this->res['status'] = 'ok';
            $this->res['data'] = $prefix1 . $prefix2 . $this->unixTime;
            // return $prefix1 . $prefix2 . $this->unixTime;
        } elseif ($prefix1 && $prefix2 && $prefix3) {
            $this->res['status'] = 'ok';
            $this->res['data'] = $prefix1 . $prefix2 . $prefix3 . $this->unixTime;
            // return $prefix1 . $prefix2 . $prefix3 . $this->unixTime;
        }
        return $this->res;
    }

    /**
     *解析编号
     */
    // public function parse($prefixNum = 2, $prefix1Len = 1, $prefix2Len = null, $prefix3Len = null)
    // {
    //     if ($prefixNum == 1) {
    //     } elseif ($prefixNum == 2) {
    //     } elseif ($prefixNum == 3) {
    //     } else {
    //         return '$prefixNum只能是1或2或3';
    //     }
    // }
}
