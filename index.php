<?php
require_once './wechat.inc.php';
$wechat = new Wechat();
if($_GET["echostr"]){
    $wechat->valid();
}else{
    $wechat->responseMsg();
}
