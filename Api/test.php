<?php



if (file_exists("../Config/Config.php")) {
    // echo "test加载了";
    include_once "../Config/Config.php";
} else {
    echo "不存在";
}

$classify = $DB->selectAll("SELECT * FROM classify WHERE state='usable'");


// echo "<hr>";
// echo "获取数据数据1111111111111111111111111111111111111111111111";
var_dump($classify);
