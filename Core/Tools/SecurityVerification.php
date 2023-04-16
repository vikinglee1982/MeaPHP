<?php
/*
 * @描述: 入参安全过滤;用户提交数据合法性验证,主要针对字符串（是否存在sql注入风险）
 *        框架只用于服务端；不用考虑页面安全问题；主要考虑接口入参的验证；是否有sql注入风险
 * @Author: Viking
 * @version: 1.0
 * @Date: 2023-03-24 00:24:37
 * @LastEditors: Viking
 * @LastEditTime: 2023-04-16 10:16:11
 */

namespace MeaPHP\Core\Tools;

class SecurityVerification
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
    public function Risk($str)
    {

        //将用户数据的字母转换成小写字符，仅用于验证，如果没有风险，返回依然时原数据

        $lowercase = strtolower($str);
        //sql关键字
        $sqlPreg = 'select|insert|update|delete|union|into|group by|extractvalue|mysql_query\(|mysql_connect\(|sprintf\(|is_numeric\(|';
        //代码执行危险函数
        $funPreg = 'eval\(|assert\(|preg_replace\(|create_function\(|array_map\(|call_user_func\(|call_user_func_array\(|';
        //命令执行危险函数
        $CommandPreg = 'updatexml|sleep|system\(|exec\(|shell_exec\(|passthru\(|';
        //文件包含危险函数
        $incluePreg = 'load_file|outfile|require\(|inclue\(|require_once\(|include_once\(|';
        //SSRF的危险函数
        $ssrfPreg = 'file_get_contents\(|fsockopen\(|curl_exec\(|readfile\(|';
        //XXE的危险函数
        $xxePreg = 'simplexml_load_string\(|asxml\(|simplexml_load_file\(|simplexml_import_dom\(|';
        //文件操作危险函数
        $filePreg = 'unlink\(|copy\(|highlight_file\(|show_source\(|fopen\(|parse_ini_file\(|fread\(|';
        //敏感信息的危险函数
        $infoPreg = 'echo|_server|phpinfo\(|getenv\(|get_current_user\(|getlastmod\(|ini_get\(|glob\(|';
        // 反序列化危险函数
        $serializationPreg = 'serialize\(|unserialize\(|__|';
        $scriptPreg = 'script|/scrip|';
        $preg = $sqlPreg . $funPreg . $CommandPreg . $incluePreg . $ssrfPreg . $xxePreg . $filePreg . $infoPreg . $serializationPreg . $scriptPreg;
        $preg = trim($preg, '|');
        // $risk = preg_match('~select|insert|update|delete|union|into|group by|extractvalue|updatexml|sleep|load_file|outfile|$_SERVER|script|/scrip|is_numeric\(|~', $lowercase);
        $risk = preg_match('~' . $preg . '~', $lowercase);

        if ($risk) {

            $this->res['status'] = "error";
            $this->res['msg'] = '参数风险警告';
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
            // $str = str_replace("%", "\%", $str); // 把 '%'过滤掉


            $str = str_replace("&", "", $str);

            // $str = str_replace("\"", "", $str);
            $str = str_replace("<", "《", $str);
            $str = str_replace(">", "》", $str);

            //使用上面的方法解决问题，下面的方法会出现很多的页面编码
            // $str = str_replace("&", "&amp", $str);
            // $str = str_replace("<", "&lt", $str);
            // $str = str_replace(">", "&gt", $str);

            $str = str_replace("'", "’", $str); // 把一个’改成一个中文’（这个非常重要）
            $str = str_replace(";", "；", $str); //堆叠查询;在SQL中，分号（;）是用来表示一条sql语句的结束。
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
