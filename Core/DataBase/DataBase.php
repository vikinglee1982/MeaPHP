<?php
/*
 * @描述:
 * @Author: Viking
 * @version: 1.0
 * @Date: 2023-03-05 17:53:22
 * @LastEditors: vikinglee1982 87834084@qq.com
 * @LastEditTime: 2024-08-28 16:08:29
 */


namespace MeaPHP\Core\DataBase;


class DataBase
{
    private static $DBobj = null;
    private $host;
    private $username;
    private $password;
    private $dbname;
    private $hostport;
    private $charset;
    private $Debug;
    private $link;

    //阻止外部克隆书库工具类
    private function __clone() {}

    //构造方法初始化，属性赋值，准备连接
    private function __construct($dbkey)
    {
        if ($dbkey) {
            $this->host     = $dbkey['MySQL']['host'];
            $this->username = $dbkey['MySQL']['username'];
            $this->password = $dbkey['MySQL']['password'];
            $this->dbname   = $dbkey['MySQL']['dbname'];
            $this->hostport = $dbkey['MySQL']['hostport'];
            $this->charset =  $dbkey['MySQL']['charset'];
            $this->Debug   = $dbkey['Debug'];

            //调用类内连接数据库方法
            $this->connect();
        } else {
            return false;
        }
    }

    //连接数据库

    private function connect()
    {

        // echo ($this->host . "+" . $this->username . "+" . $this->password . "+" . $this->dbname);
        $this->link = mysqli_connect($this->host, $this->username, $this->password, $this->dbname, $this->hostport);
        //检查连接
        if (!$this->link) {
            // if ($this->online) {
            //     //上线运营模式：阻断执行，不返回任何数据
            //     die();
            // } else {
            //     //调试模式：阻断执行，返回失败原因
            //     die("Connection failed(连接失败):" . mysqli_connect_error());
            // }

            if ($this->Debug) {
                die("Connection failed(连接失败):" . mysqli_connect_error());
            } else {
                die();
            }
        }
        //如果连接成功，设置数据库字符集，非外部传入
        mysqli_set_charset($this->link, $this->charset);
        // echo "连接数据库成功<br>";
    }

    //内部产生静态对象
    public static function active($dbkey)
    {
        // var_dump($dbkey);
        if (!self::$DBobj instanceof self) {
            //如果不存在，创建保存
            self::$DBobj = new self($dbkey);
        }
        return self::$DBobj;
    }

    //查询数据，执行select
    private function select($sql)
    {
        //将$sql转换为小写
        // $sql = strtolower($sql);

        //echo $sql;
        //判断用户使用的是查询语句
        if (substr($sql, 0, 6) != 'select' && substr($sql, 0, 6) != 'SELECT') {



            //阻断执行
            die();
        } else {
            //返回数据
            return mysqli_query($this->link, $sql);
        }
    }

    //查询单行数据
    public function selectOne($sql, $type = 3)
    {
        //执行sql语句，接收返回结果集对象
        $res   = $this->select($sql);
        $types = array(
            1 => MYSQLI_NUM,
            2 => MYSQLI_BOTH,
            3 => MYSQLI_ASSOC,

        );
        //返回一维数组//如果查询不到会报错
        // var_dump($res);
        if ($res) {
            return mysqli_fetch_array($res, $types[$type]);
        } else {
            return 0;
            // return $sql;
        }
    }

    //查询多行数据
    public function selectAll($sql, $type = 3)
    {
        //执行sql语句，接收返回结果集对象
        $res   = $this->select($sql);
        $types = array(
            1 => MYSQLI_NUM,
            2 => MYSQLI_BOTH,
            3 => MYSQLI_ASSOC,

        );
        return mysqli_fetch_all($res, $types[$type]);
    }

    //增删改数据库数据
    public function execute($sql)
    {
        //将$sql转换为小写
        // $sql = strtolower($sql);
        // echo $sql;
        //判断用户使用的不是查询语句

        if (substr($sql, 0, 6) == 'select' || substr($sql, 0, 6) == 'SELECT') {
            if ($this->Debug) {
                //调试模式返回错误信息
                echo "该方法不能执行select，只能执行增删改语句";
            } else {
                //阻断执行
                die();
            }
        } else {
            //可以执行
            // echo "<hr>";
            // echo "执行阶段";
            // echo "<hr>";

            mysqli_query($this->link, $sql);
            //返回受影响的行数
            $rowNum = mysqli_affected_rows($this->link);
            if ($rowNum > 0) {
                return $rowNum;
            }
        };
    }

    /**
     * @description: 获取数据表中的数据条数
     * @param {string}  $tableAndCondition
     * @hint 格式说明(SELECT * FROM $tableAndCondition)
     * @return {int}
     */
    public function rowNum(string $table, string $condition)
    {
        //执行sql语句，并返回结果集
        // SELECT COUNT(*) FROM tablename WHERE condition

        $res = $this->select("SELECT COUNT(*) AS total FROM $table WHERE $condition");
        //在结果集中或的记录数，并返回
        // return mysqli_num_rows($res);
        return mysqli_fetch_array($res, MYSQLI_NUM)[0];
    }

    //返回刚刚插入的行的id
    //mysql_insert_id() 函数返回上一步 INSERT 操作产生的 ID。如果上一查询没有产生 AUTO_INCREMENT 的 ID，则 mysql_insert_id() 返回 0
    public function getInsertId(): int
    {
        $pid = mysqli_insert_id($this->link);

        return $pid;
    }

    //析构方法，关闭连接
    public function __destruct()
    {
        if ($this->link) {
            //连接成功时，关闭数据库，如果关闭失败屏蔽错误，防止暴露文件地址
            @mysqli_close($this->link);
        } else {
            if ($this->Debug) {
                //如果是调试模式：返回失败原因
                return mysqli_close($this->link);
            }
        }
    }
}
