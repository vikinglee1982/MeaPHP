<?php

/*
@文件用途{
 ****入参安全过滤
 ****用户提交数据合法性验证,主要针对字符串（是否存在sql注入风险）
}

@实现思路{
    框架只用于服务端；不用考虑页面安全问题；主要考虑接口入参的验证；是否有sql注入风险
    ->参数普通参数 返回原参数
    -》验证字符串

}


}
 */

namespace MeaPHP\Core\Tools;

class Verify
{
    private static $obj = null;
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

    //验证字符串方法
    public function sqlRisk($str)
    {
        // $risk = preg_match('~select|insert|and|or|update|delete|\/\*|\*|\.\.\/|\.\/|union|into|load_file|outfile~', $str);
        //上边过滤的过于严格，使用下面的过滤，标点符号很容易被过滤and和or，被过滤最多的词

        //将用户数据的字母转换成小写字符，仅用于验证，如果没有风险，返回依然时原数据

        $lowercase = strtolower($str);

        $risk = preg_match('~select|insert|update|delete|union|into|load_file|outfile~', $lowercase);
        // var_dump($risk);

        if ($risk) {

            $this->res['status'] = "error";
            $this->res['msg'] = 'SQL注入风险；含有关键字';
            return $this->res;
            // return "Warning SQL注入风险";
            exit();
        } elseif ($lowercase == 'undefined' || $lowercase == 'null') {
            $this->res['status'] = "error";
            $this->res['msg'] = '无意义数据';
            return $this->res;
            // return false;
            // 无意义数据;
            exit();
        } else {

            //需要修改的
            //这里将字符串中特殊字符改写成不会影响sql的安全字符
            $str = str_replace("_", "\_", $str); // 把 '_'过滤掉
            $str = str_replace("%", "\%", $str); // 把 '%'过滤掉

            $str = str_replace("\"", "", $str);
            $str = str_replace("&", "", $str);
            $str = str_replace("<", "《", $str);
            $str = str_replace(">", "》", $str);

            //使用上面的方法解决问题，下面的方法会出现很多的页面编码
            // $str = str_replace("&", "&amp", $str);
            // $str = str_replace("<", "&lt", $str);
            // $str = str_replace(">", "&gt", $str);

            $str = str_replace("'", "’", $str); // 把一个’改成一个中文’（这个非常重要）
            $str = str_replace(";", "；", $str);
            $str = str_replace("(", "（", $str);
            $str = str_replace(")", "）", $str);
            $str = str_replace(",", "，", $str);
            $str = str_replace("?", "？", $str);
            $str = str_replace("*", "※", $str);
            $str = str_replace("$", "＄", $str);
            $str = str_replace("[", "【", $str);
            $str = str_replace("]", "】", $str);

            //防止16进制注入o
            $str = str_replace("0x", "0 x", $str);

            // $str = str_replace("or", "&gt", $str);
            // $str = str_replace("OR", "&gt", $str);

            return $str;
        }
    }
}
