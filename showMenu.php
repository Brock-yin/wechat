<?php
//加载类文件
require_once './wechat.inc.php';
header("content-type:text/html;charset=utf-8");
$wechat = new Wechat();
$wechat->showMenu();