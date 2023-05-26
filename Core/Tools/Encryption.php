<?php
/*
 * @描述: 
 * @Author: Viking
 * @version: 1.0
 * @Date: 2023-05-04 23:18:04
 * @LastEditors: Viking
 * @LastEditTime: 2023-05-04 23:31:40
 */

namespace  MeaPHP\Core\Tools;

class Encryption
{

    public static $Obj;
      //设置AES秘钥
      private static $aes_key = 'viking'; //此处填写前后端共同约定的秘钥
    //内部产生静态对象
    public static function active()
    {
        // var_dump($config);
        // echo "开始创建<hr>";
        if (!self::$Obj instanceof self) {
            // echo "不存在，创建了<hr>";
            //如果不存在，创建保存
            self::$Obj = new self();
        }
        return self::$Obj;
    }
    //阻止外部克隆书库工具类
    private function __clone()
    {
    }

    //构造方法初始化，属性赋值，准备连接
    private function __construct()
    {
    }
  

    /**
     * 加密
     * @param string $str    要加密的数据
     * @return bool|string   加密后的数据
     */
    public static function AESEncrypt($str)
    {

        return base64_encode(openssl_encrypt($str, "AES-128-ECB", self::$aes_key, OPENSSL_RAW_DATA));
    }

    /**
     * 解密
     * @param string $str    要解密的数据
     * @return string        解密后的数据
     */
    public static function  AESDecrypt($str)
    {

        return openssl_decrypt(base64_decode($str), "AES-128-ECB", self::$aes_key, OPENSSL_RAW_DATA);
    }
}
