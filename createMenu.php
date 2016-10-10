<?php
//创建自定义菜单
require_once './wechat.inc.php';
header('content-type:text/html;charset=utf-8');
$createmenu = new Wechat();
$flag = $createmenu->createMenu();
//返回的是一个json格式的$flag {"errcode":0,"errmsg":"ok"}
$flag = json_decode($flag);
if($flag->errmsg == 'ok'){
   
    echo '创建菜单成功！！';
}else{
    
    echo '创建菜单失败！！';
}