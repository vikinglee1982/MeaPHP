<?php
/*
 * @Author: vikinglee1982 87834084@qq.com
 * @Date: 2024-03-11 15:47:57
 * @LastEditors: vikinglee1982 87834084@qq.com
 * @LastEditTime: 2024-04-05 16:08:41
 * @FilePath: \工作台\Servers\huayun_server\MeaPHP\Export\Export.php
 * @Description: 输出类，所有用户的请求返回的数据都在这里返回;考虑是否使用静态，简便使用方法
 * 
 * **API返回格式**
1.recode

+ 2000-2999 成功
+ 3000-3999 debug模式下：不会退出系统；上线：高权重错误：权限错误、安全等需要剔除系统错误；这里考虑是否需要将ip加入黑名单
+ 4000-4999 参数错误
+ 5000-5999 系统程序错误(业务错误)
+ 6000-6999 Api路径或文件错误
+ 7000-7999 调试模式：错误
+ 8000-8999 调试模式：配置错误

2.data

+ 成功:$data['自定义']
+ 失败:$data['msg'] = "错误信息";
+ 响应时间： $data['ms'] = date('Y-m-d h:i:s', time());
 * 
 * 
 */

namespace MeaPHP\Export;

class Export
{

    private static $obj = null;


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
        // echo "<hr>";
        // echo "建立了";
        // var_dump($dbkey);
        if (!self::$obj instanceof self) {
            //如果不存在，创建保存
            self::$obj = new self();
        }
        return self::$obj;
    }
    public static function send($responseBody = [], $recode = 3000, $data = [])

    {
      
        // // $data['err1'] = '每次执行了操作1212';
        if (count($responseBody) > 0) {

            echo json_encode($responseBody);
        } else {
            // $data['HTTP_ORIGIN'] =  $_SERVER['HTTP_ORIGIN'];
            $data['ms'] = date('Y-m-d h:i:s', time());
            $responseBody['recode'] = $recode;
            $responseBody['data'] = $data;
            echo json_encode($responseBody);
        }
        return;
    }
}
