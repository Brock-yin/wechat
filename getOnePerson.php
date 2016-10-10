<?php
require_once './wechat.inc.php';

$wechat = new Wechat();

//设定一个要查询的openId
$openId = 'ouv3BwocXnMVyIF2bpAk092D7o3Q';
$wechat->getmsgOne($openId);