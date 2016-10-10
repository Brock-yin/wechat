<?php
//用来存放一些微信存放接口的一些请求方法
//加载配置文件的信息
require_once './wechat.cfg.php';

class Wechat{
    //定义从配置文件中获取的一些变量信息
    private $appid ;
    private $appsecret;
    private $token;
    //定义构造函数
    public function __construct(){
        $this->appid = APPID;
        $this->appsecret = APPSECRET;
        $this->token = TOKEN;
    }
 
    public function valid()
    {
        $echoStr = $_GET["echostr"];
    
        //valid signature , option
        if($this->checkSignature()){
            echo $echoStr;
            exit;
        }
    }
    
    public function responseMsg()
    {
        //get post data, May be due to the different environments
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
    
        //extract post data
        if (!empty($postStr)){
            /* libxml_disable_entity_loader is to prevent XML eXternal Entity Injection,
             the best way is to check the validity of xml by yourself */
            libxml_disable_entity_loader(true);
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $fromUsername = $postObj->FromUserName;
            $toUsername = $postObj->ToUserName;
            $keyword = trim($postObj->Content);
            $time = time();
            $textTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
							<Content><![CDATA[%s]]></Content>
							<FuncFlag>0</FuncFlag>
							</xml>";
            if(!empty( $keyword ))
            {
                $msgType = "text";
                $contentStr = "Welcome to wechat world!";
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                echo $resultStr;
            }
        }
    }  
    private function checkSignature()
    {
        // you must define TOKEN by yourself
        if (!defined("TOKEN")) {
            throw new Exception('TOKEN is not defined!');
        }
    
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
    
        $token = $this->token;
        $tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
    
        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }
    //请求接口函数
    function request($url,$https=true,$method='get',$data=null){
         
        $ch = curl_init($url);
    
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($ch, CURLOPT_HEADER, 1);
    
        if($https === true){
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
    
        if($method == 'post'){
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
    
    
        $str = curl_exec($ch);
    
        curl_close($ch);
        return $str;
    }
    //获取access_token的信息
    function getAccessToken(){
        $url  ='https://api.weixin.qq.com/cgi-bin/token?';
        $url .='grant_type=client_credential&appid='.$this->appid;
        $url .='&secret='.$this->appsecret;
        $access_token = $this->request($url);
        //将获取到的json格式的access_token需要的部分提取出来(对象的形式)
        $access_token = json_decode($access_token)->access_token;
        return $access_token;
    }
    //删除公众号中的菜单
    function delMenu(){
        //获取access_token
        $access_token = $this->getAccessToken();
        //进行删除菜单的操作
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/delete?access_token='.$access_token;
        //get传参，https方式，url
        return $this->request($url);
    }
    //创建菜单
    function createMenu(){
        //获取access_token的值，返回结果为一个json字符串格式
        $access_token = $this->getAccessToken();
    
    
        //echo $access_token;die;
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$access_token;
        $data = '{
                     "button":[
                     {
                          "type":"click",
                          "name":"音如雨",
                          "key":"V1001_TODAY_MUSIC"
                      },
                      {
                           "name":"小公举",
                           "sub_button":[
                           {
                               "type":"view",
                               "name":"百度",
                               "url":"http://www.baidu.com/"
                            },
                            {
                               "type":"view",
                               "name":"视频",
                               "url":"http://v.qq.com/"
                            },
                            {
                               "type":"click",
                               "name":"赞一下我们",
                               "key":"V1001_GOOD"
                            }]
                       }]
                 }';
        // url https post
        return $this->request($url,true,'post',$data);
    }
    //展示菜单栏
    function showMenu(){
         
        $access_token = $this->getAccessToken();
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/get?access_token='.$access_token;
        //调用请求函数
        $content = $this->request($url);//返回的是一个json格式的字符串
        //将json格式的字符串转化为对象格式
        $content = json_decode($content);
        echo '<pre>' ;
        var_dump($content);
    }
    //创建二维码
    function createQRcode(){
        //获取access_token数据
        $access_token = $this->getAccessToken();
        //满足request
        $url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$access_token;
        //post方式传参，https模式
        $data ='{"expire_seconds": 604800,';
        $data.='"action_name": "QR_SCENE",';
        $data.='"action_info": {"scene": {"scene_id": 123}}}';
    
        //调用request函数将数据闯到微信服务器
        $result = $this->request($url,true,'post',$data);//返回的是一个json格式，带有ticket
        $ticket = json_decode($result)->ticket;
    
        //根据获得的ticket来获取要生成的呃二维码图片,get方式https，
        $url = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.$ticket;
        $img = $this->request($url);
        //将图片资源下载到本地,因为所获得的img是一个资源对象
        file_put_contents('QRcode.jpg', $img);
         
    }
    //展示用户列表
    function showPerons(){
        //get方式，https方式，
        //获取access_token
        $access_token = $this->getAccessToken();
        $url = 'https://api.weixin.qq.com/cgi-bin/user/get?access_token='.$access_token;
        //获得的是一个json数据
        $content = $this->request($url);
        $content = json_decode($content);
        echo '总人数为：'.$content->total.'<br />';
        echo '获取到的的总人数为：'.$content->count.'<br />';
        //循环输出每一个openID
        $content = $content->data->openid;
         
        //循环输出opernID
        foreach ($content as $key=>$value){
            echo $value.'<br />';
    
        }
    }
    //获取一个用户的基本信息
    function getmsgOne($openid){
        //https://api.weixin.qq.com/cgi-bin/user/info?access_token=ACCESS_TOKEN&openid=OPENID&lang=zh_CN
        $access_token = $this->getAccessToken();
        $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';
        //获得的结果为一个json数据
        $content = $this->request($url);
        //转化为一个对象数据
        $content = json_decode($content);
        //获取昵称
        echo '用户的昵称是：'.$content->nickname;
        //获取性别
        switch ($content->sex){
            case 0: echo '<br /> 未知性别~'; break;
            case 1: echo '<br /> 男性~'; break;
            case 2: echo '<br /> 女性~'; break;
            default: break;
        }
        //获取用户所在的城市
        echo '所在的城市是：'.$content->province;
        //获取用户的头像
        echo "<img src=".$content->headimgurl." />";
    }
}











