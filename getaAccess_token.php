<?php
include './wechat.inc.php';
$wechat = new Wechat();
$access_token = $wechat->getAccessToken();

//返回的access_token是一个字符串
echo $access_token;