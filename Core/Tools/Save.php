<?php


namespace  MeaPHP\Core\Tools;

/**
 * 文件上传，接受保存到服务器

 */

class Save
{

    private static $obj = null;
    private $folderName;
    private $fileType;
    private $file;
    public $res = array(
        // 'status' => 'error',
        // //只有2种状态 ok/error
        // 'data' => null,
        // //正确：返回数据
        // 'msg' => null,
        //错误：返回错误原因
    );
    //用户多图片数组储存时临时储存返回结果用
    private $tempRes = array();



    //阻止外部克隆书库工具类
    private function __clone()
    {
    }

    //私有化构造方法初始化，禁止外部使用
    private function __construct()
    {
        $this->res = array();
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
    //储存多图
    public function batchImage($arr)
    {
        //通过递归方式调用
        //先验证参数是否正确
        if (!is_array($arr)) {
            $this->res['status'] = 'error';
            $this->res['msg'] = '入参必须是数组';
        } else {
            //检查数组的数据结构
            $fault = false;
            foreach ($arr as $k => $v) {
                if (!$v['resource'] || !$v['path']) {
                    $fault = true;
                    break;
                }
            }
            //结构式错误的
            if ($fault) {
                $this->res['status'] = 'error';
                $this->res['msg'] = '入参的数组结构错误;
                [
                    0:{
                        resource:图片资源,
                        path:从项目根路径下开始的储存路径,
                    }
                    1:{
                        resource:图片资源,
                        path:从项目根路径下开始的储存路径,
                    }
                ]';
            } else {

                $this->recursion($arr);

                // $this->res['status'] = 'ok';
                // $this->res['msg'] = $arr;
            }
            return $this->res;
        }
    }
    //递归循环调用储存
    private function recursion($arr, $i = 0)
    {
        $res = $this->image($arr[$i]['resource'], $arr[$i]['path']);
        if ($res['status'] == 'ok') {
            //临时储存已经储存的图片路径
            array_push($this->tempRes, $this->res['data']);
            $i++;
            if ($arr[$i]) {
                $this->recursion($arr, $i);
            } else {
                $this->res['status'] = 'ok';
                $this->res['data'] = $this->tempRes;
                return $this->$res;
            }
        } else {
            return $this->res;
        }
    }
    //生成毫秒级时间戳
    public function image($file = null, $folderName = null, $fileName = null)

    {
        // return $file;
        // return  'jianlile ';
        //将路径全部转为小写
        $folderName = strtolower($folderName);
        // //判断入参的文件类型，必须时图片格式
        $this->fileType = $file['type'];
        if (!$file) {
            $this->res['status'] = "error";
            $this->res['msg'] = '请缺少入参image文件';
            // return "error:请缺少入参image文件";
        } elseif ($this->fileType != 'image/jpeg' && $this->fileType != 'image/png' && $this->fileType != 'image/webp' && $this->fileType != 'image/gif') {

            $this->res['status'] = "error";
            $this->res['msg'] = '文件类型支持[gif/jpg/jpge/png//webp]';
            // return "error:文件类型支持[gif/jpg/jpge/png//webp]";
            //image/jpeg;image/png;image/webp;image/gif
            // return $file['type'];
        } elseif (!$folderName) {
            $this->res['status'] = "error";
            $this->res['msg'] = '缺少文件夹目录（从项目根路径下开始的目录）';
            // return "error:缺少文件夹目录（项目根路径下的目录）";
        } elseif (strpos($folderName, 'undefined') || strpos($folderName, 'null')) {
            $this->res['status'] = "error";
            $this->res['msg'] = '文件夹目录中不能存在undefined或者null等字段';
            // return "error:缺少文件夹目录（项目根路径下的目录）";
        } else {
            $this->folderName = $folderName;

            $this->file       = $file;

            if (!$fileName) {
                //如果用户没有传进文件名，就生成一个，不重复的文件名
                $fileName = $this->createFileName();
            }

            //合成需要存放文件的路径
            // $userDir = iconv('utf-8', 'gbk', $this->basicsPath . '/' . $folderName . '/' . $fileType);
            if (substr($folderName, 0, 1) !== '/') {
                $folderName = '/' . $folderName;
            }

            $userDir = iconv('utf-8', 'gbk', $_SERVER['DOCUMENT_ROOT']  . $folderName);


            if (!is_dir($userDir)) {

                mkdir(iconv("UTF-8", "GBK", $userDir), 0777, true);
            }
            //开始储存

            $src = $userDir . '/' . $fileName;


            if (file_exists($src)) {
                $this->res['status'] = "error";
                $this->res['msg'] = '文件名重复';
                // $this->res['msg'] = $src;
                // return "error:已经有这个文件了";
            } else {
                if (move_uploaded_file($file['tmp_name'], $src)) {
                    //储存完成合成路径返回
                    //
                    // $url = str_replace($_SERVER['DOCUMENT_ROOT'], $_SERVER['HTTP_HOST'], $src);
                    $url = str_replace($_SERVER['DOCUMENT_ROOT'], '', $src);

                    $this->res['status'] = "ok";
                    $this->res['data'] =  $url;
                    // return $url;
                } else {
                    //储存失败
                    $this->res['status'] = "error";
                    $this->res['msg'] = '储存失败：' . $src;
                    // $data['src'] = $src;
                    // return 3000;
                    // return $src;
                }
            }
        }
        return $this->res;
    }

    private function createFileName()
    {

        //获取文件后缀名
        $extension = substr(strrchr($this->file['name'], '.'), 1);
        // md5(uniqid(md5(microtime(true)), true));
        return md5($this->folderName . md5(uniqid(md5(microtime(true)), true)) . $this->fileType) . '.' . $extension;
    }
}
