<?php

/*
@文件用途{
 ****数据的格式验证
 ****
}
@实现思路{
    验证手机号码；身份证号码等等固定格式的数据是否符合要求
}
 */

namespace MeaPHP\Core\Tools;

class FormatValidation
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
    //验证邮箱格式是否正确
    public function email($email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->res['status'] = 'error';
            $this->res['msg'] = '初级验证结果：错误的Email';
            // return false;
        } else {
            $this->res['status'] = 'ok';
            $this->res['data'] =  $email;
            // return $email;
        }
        return $this->res;
    }
    //验证日期是否正确
    public function date($dateNum)
    {
        //将入参的日期格式，通过strtotime格式化成Unix 时间戳，然后格式化时间戳和入参数据对比，如果相等证明时时间格式，并格式化输出
        $date = strtotime($dateNum);
        if ($dateNum == (date("Y-m-d", $date)) || $dateNum == (date("Y-m-j", $date)) || $dateNum == (date("Y-n-d", $date)) || $dateNum == (date("Y-n-j", $date))) {
            // return date('Y-m-d', $date);
            $this->res['status'] = 'ok';
            $this->res['data'] =  date('Y-m-d', $date);
        } else {
            $this->res['status'] = 'error';
            $this->res['msg'] = '错误的日期数据';
            // return false;
        }
        return $this->res;
    }
    //验证身份证号码的正确性
    public function idNo($idcard)
    {
        //基本格式校验
        if (!preg_match('/^\d{17}[0-9xX]$/', $idcard)) {
            // return false;
            $this->res['status'] = 'error';
            $this->res['msg'] = '错误的身份证号码';
        }

        // 只能是18位
        if (strlen($idcard) != 18) {
            // return false;
            $this->res['status'] = 'error';
            $this->res['msg'] = '身份证号码只能是18位';
        }
        // 取出本体码
        $idcard_base = substr($idcard, 0, 17);
        // 取出校验码
        $verify_code = substr($idcard, 17, 1);
        // 加权因子
        $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
        // 校验码对应值
        $verify_code_list = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
        // 根据前17位计算校验码
        $total = 0;
        for ($i = 0; $i < 17; $i++) {
            $total += substr($idcard_base, $i, 1) * $factor[$i];
        }
        // 取模
        $mod = $total % 11;
        // 比较校验码
        if ($verify_code == $verify_code_list[$mod]) {
            // return $idcard;
            $this->res['status'] = 'ok';
            $this->res['data'] = $idcard;
            $this->res['msg'] = '正确的身份证号码';
        } else {
            $this->res['status'] = 'error';
            $this->res['msg'] = '错误的身份证号码验证码';
            $this->res['data'] = $idcard;
            // return false;
        }
        return $this->res;
    }
    public function phoneNumber($telnum)
    {

        //@2017-11-25 14:25:45 https://zhidao.baidu.com/question/1822455991691849548.html
        //中国联通号码：130、131、132、145（无线上网卡）、155、156、185（iPhone5上市后开放）、186、176（4G号段）、175（2015年9月10日正式启用，暂只对北京、上海和广东投放办理）,166,146
        //中国移动号码：134、135、136、137、138、139、147（无线上网卡）、148、150、151、152、157、158、159、178、182、183、184、187、188、198
        //中国电信号码：133、153、180、181、189、177、173、149、199
        $g  = "/^1[34578]\d{9}$/";
        $g2 = "/^19[89]\d{8}$/";
        $g3 = "/^166\d{8}$/";
        if (preg_match($g, $telnum) || preg_match($g2, $telnum) || preg_match($g3, $telnum)) {
            // return $telnum;
            $this->res['status'] = 'ok';
            $this->res['data'] = $telnum;
        } else {
            // return false;
            $this->res['status'] = 'error';
            $this->res['msg'] = '错误的手机号码';
        }

        return $this->res;

        // $telRegex = "(^((13[0-9])|(14[5,7,9])|(15[^4])|(18[0-9])|(17[0,1,3,5,6,7,8]))\\d{8}$)";
        // // "[1]"代表第1位为数字1，"[358]"代表第二位可以为3、5、8中的一个，"\\d{9}"代表后面是可以是0～9的数字，有9位。

    }
}
