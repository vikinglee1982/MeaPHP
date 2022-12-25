<?php

// include_once $_SERVER['DOCUMENT_ROOT'] . '/Config/Config.php';
// echo $_SERVER['DOCUMENT_ROOT'] . '/Config/Config.php';
if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/Config/Config.php')) {
    // echo "test加载了";
    include_once $_SERVER['DOCUMENT_ROOT'] . '/Config/Config.php';
} else {
    echo "error:引入配置文件失败";
}

$id = $BuildID->getID();

echo $id;
// $classify = $DB->selectAll("SELECT * FROM classify WHERE state='usable'");


// echo "<hr>";
// echo "获取数据数据1111111111111111111111111111111111111111111111";
// var_dump($classify);
