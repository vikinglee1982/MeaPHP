<?php


include_once $_SERVER['DOCUMENT_ROOT'] . '/MeaPHP/AutoLoad/AutoLoad.php';

// echo  $_SERVER['DOCUMENT_ROOT'] . '/MeaPHP/AutoLoad/AutoLoad.php';

// if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/MeaPHP/AutoLoad/AutoLoad.php')) {
//     echo "<br>";
//     echo "加载了";
//     include_once $_SERVER['DOCUMENT_ROOT'] . '/MeaPHP/AutoLoad/AutoLoad.php';
// } else {
//     echo "不存在";
// }

// echo rand(0, 10000);








$classify = $DB->selectAll("SELECT * FROM classify WHERE state='usable'");
echo "<hr>";
echo "获取数据数据";
var_dump($classify);

// $config = new Config();
// $res = $run->autoLoad();

// echo $res;
// AutoLoad\AutoLoad::start($config);
