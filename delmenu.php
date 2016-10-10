<?php
//创建自定义菜单
require_once './wechat.inc.php';
header('content-type:text/html;charset=utf-8');
$delmenu = new Wechat();
$flag = $delmenu->delMenu();
//返回的是一个json格式的$flag {"errcode":0,"errmsg":"ok"}
$flag = json_decode($flag);
if($flag->errmsg == 'ok'){
    echo '删除菜单成功！！';
}else{
    echo '删除菜单失败！！';
}