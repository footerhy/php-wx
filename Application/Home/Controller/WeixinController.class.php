<?php
namespace Home\Controller;

use Think\Controller;
use Think\Log;

//define your token
define("TOKEN", "alofans");
//公众平台沙箱测试账号
define("APPID", "wxa39fbc09f34275ac");
define("APPSECRET", "4f52619988ada788d40f047a5d15583b");
class WeixinController extends Controller{
    public function index(){
        $this->display();
    }
    
    
    //微信验证
    public function weixin(){
        //1、将timestamp,nonce,toke按字典排序
        //2、讲排序后的参数拼接后用sha1加密
        //3、将加密后的参数与signature进行对比，判断请求是否来自微信
        
        $echoStr = $_GET["echostr"];
        //Log::write(TOKEN . ' $echoStr : ' . $echoStr);
        //valid signature , option
        if($this->checkSignature() && $echoStr){
            Log::write('验证成功：' . $echoStr);
            echo $echoStr;
            exit;
        }else{
            $this->responseMsg();
        }
        Log::write('不需要通过验证：' . $echoStr);
    }
    
    public function responseMsg(){
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
            $msgType = $postObj->MsgType;
            $event = $postObj->Event;
            $time = time();
            //默认文本模板
            $textTpl = "<xml>
						<ToUserName><![CDATA[%s]]></ToUserName>
						<FromUserName><![CDATA[%s]]></FromUserName>
						<CreateTime>%s</CreateTime>
						<MsgType><![CDATA[%s]]></MsgType>
						<Content><![CDATA[%s]]></Content>
						</xml>";
    
            if($msgType == 'event' ){
                //订阅
                if($event == 'subscribe'){
                    if('qrscene_' . 2000 == $postObj->EventKey){
                        $content = '临时二维码关注';
                    }else if('qrscene_' . 3000 == $postObj->EventKey){
                        $content = '永久二维码关注';
                    }else{
                        $content = "欢迎关注我们的微信公众号" . $postObj->EventKey;
                    }
                    $msgType = "text";
                    $contentStr = $content;
                    $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                    echo $resultStr;
                }else if(strtolower($event) == 'click'){
                    //自定义菜单中的event时间
                    $msgType = "text";
                    $contentStr = "EventKey值为：" . $postObj->EventKey;
                    $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                    echo $resultStr;
                }else if(strtolower($event) == 'scan'){
                    //第二次扫二维码
                    if(2000 == $postObj->EventKey){
                        //临时二维码
                        $content = '临时二维码进入';
                    }else if(3000 == $postObj->EventKey){
                        //永久二维码
                        $content = '永久二维码进入';
                    }
                    $msgType = "text";
                    $contentStr = $content;
                    $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                    echo $resultStr;
                }
            }else if($msgType == 'text'){
                //文本
                if($keyword == '1'){
                    $msgType = "text";
                    $contentStr = "Welcome to wechat world!";
                    $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                    echo $resultStr;
                }else if($keyword == 'tuwen1'){
                    $arr = array(
                        array(
                            'title'=>'玄驹科技',
                            'description'=>'湖南玄驹科技是一家网络科技公司.',
                            'picUrl'=>'http://www.iyi8.com/uploadfile/2016/0424/20160424115032116.jpg',
                            'url'=>'http://www.blackcolt.cn'
                        ),
                        array(
                            'title'=>'湖南优成环保科技有限公司',
                            'description'=>'优成环保科技是一家环境科技公司.',
                            'picUrl'=>'http://www.iyi8.com/uploadfile/2016/0424/20160424115033948.jpg',
                            'url'=>'http://www.blackcolt.cn'
                        ),
                    );
                    $textTpl = "<xml>
                                <ToUserName><![CDATA[%s]]></ToUserName>
                                <FromUserName><![CDATA[%s]]></FromUserName>
                                <CreateTime>%s</CreateTime>
                                <MsgType><![CDATA[news]]></MsgType>
                                <ArticleCount>" . count($arr) . "</ArticleCount>
                                <Articles>";
                    foreach ($arr as $k=> $v){
                        $textTpl .=       "<item>
                                    <Title><![CDATA[" . $v['title'] . "]]></Title>
                                    <Description><![CDATA[" . $v['description'] . "]]></Description>
                                    <PicUrl><![CDATA[" . $v['picUrl'] . "]]></PicUrl>
                                    <Url><![CDATA[" . $v['url'] . "]]></Url>
                                    </item>";
                    }
                    $textTpl .="   </Articles>
                            </xml>";
                    Log::write($textTpl);
                    echo $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time);
                }else{
                    
                    
                    
                    $ch = curl_init();
//                     $url = 'http://apis.baidu.com/apistore/weatherservice/weather?citypinyin=beijing';
                    $url = 'http://apis.baidu.com/apistore/weatherservice/cityname?cityname=' . $keyword;
                    $header = array(
                        'apikey: 3757b8aca23d0ba572238e8f46b135fa',
                    );
                    // 添加apikey到header
                    curl_setopt($ch, CURLOPT_HTTPHEADER  , $header);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    // 执行HTTP请求
                    curl_setopt($ch , CURLOPT_URL , $url);
                    $res = curl_exec($ch);
                    
                    $arr = json_decode($res,true);
                    
                    if(!empty($arr) && $arr['errMsg'] == 'success'){
                        
                        $content = $keyword . '今天' . "\n" 
                            .'天气情况:' . $arr['retData']['weather'] . "\n"
                            . '气温：' . $arr['retData']['temp'] . "\n" 
                            . '最低气温：' . $arr['retData']['l_tmp'] . "\n"
                            . '最高气温：' . $arr['retData']['h_tmp']
                            ;
                    }else{
                        $content = "请输入城市名称查询天气，如：北京";
                    }
                    
                    /* 
                    
                    
                    {
                    errNum: 0,
                    errMsg: "success",
                    retData: {
                       city: "北京", //城市
                       pinyin: "beijing", //城市拼音
                       citycode: "101010100",  //城市编码	
                       date: "15-02-11", //日期
                       time: "11:00", //发布时间
                       postCode: "100000", //邮编
                       longitude: 116.391, //经度
                       latitude: 39.904, //维度
                       altitude: "33", //海拔	
                       weather: "晴",  //天气情况
                       temp: "10", //气温
                       l_tmp: "-4", //最低气温
                       h_tmp: "10", //最高气温
                       WD: "无持续风向",	 //风向
                       WS: "微风(<10m/h)", //风力
                       sunrise: "07:12", //日出时间
                       sunset: "17:44" //日落时间
                      }    
                    }
                    

                    
                     */
                    
                    $msgType = "text";
//                     $contentStr = "您输入的内容是：" . $keyword;
                    $contentStr = $content;
                    $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                    echo $resultStr;
                }
            }
    
    
        } else{
            echo "404";
            exit;
        }
    }
    
    //发送模板消息
    public function sendTemplateMsg(){
        $accessToken = $this->getWxAccessToken();
        
        $url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $accessToken;
        $arr = array(
            "touser"=>"oLOObsxeGDRw-lNIEE6otZxOrP9E",
            "template_id"=>"3msCWCnKDibE8ldE3Z5rZRQpBGVNVhubxOJ6GsZhdOU",
            "url"=>"http://www.qq.com",
            "data"=>array(
                    "money"=>array(
                        "value"=>"586.48",
                        "color"=>"#173177"
                    ),
                    "address"=>array(
                        "value"=>"湖南省长沙市天心区友谊路雅苑国际906！",
                        "color"=>"#173177"
                    ) ,
                    "mobile"=>array(
                        "value"=>"18654854284",
                        "color"=>"#173177"
                    ) 
            )
        );
        $postJson = json_encode($arr);
        
        $res = $this->http_curl($url,'post','json',$postJson);
        var_dump($res);
    }
    
    //临时二维码
    public function getTimeQrCode(){
        header('content-type:text/html;charset=utf-8');
        //1.获取ticket票据
        //全局票据access_token,网页授权access_token，微信js-SDK jsapi_ticket
        $accessToken = $this->getWxAccessToken();
        $url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=' . $accessToken;
        //{"expire_seconds": 604800, "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": 123}}}
        $postArr = array(
            'expire_seconds'=>604800,
            'action_name'=>'QR_SCENE',
            'action_info'=>array(
                'scene'=>array(
                    'scene_id'=>2000
                )
            )
        );
        $postJson = json_encode($postArr);
        $res = $this->http_curl($url,'post','json',$postJson);
        
        $ticket = $res['ticket'];
        //使用ticket获取二维码图片
        
        $url = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . urlencode($ticket);
        
        echo '临时二维码';
        echo '<hr />';
        
        echo "<img src='" . $url . "' />";
    }
    
    //永久二维码
    public function getPermanentQrCode(){
        header('content-type:text/html;charset=utf-8');
        //1.获取ticket票据
        //全局票据access_token,网页授权access_token，微信js-SDK jsapi_ticket
        $accessToken = $this->getWxAccessToken();
        $url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=' . $accessToken;
        //{"expire_seconds": 604800, "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": 123}}}
        $postArr = array(
            'action_name'=>'QR_LIMIT_SCENE',
            'action_info'=>array(
                'scene'=>array(
                    'scene_id'=>3000
                )
            )
        );
        $postJson = json_encode($postArr);
        $res = $this->http_curl($url,'post','json',$postJson);
        
        $ticket = $res['ticket'];
        //使用ticket获取二维码图片
        $url = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . urlencode($ticket);
        echo '永久二维码';
        echo '<hr />';
        echo "<img src='" . $url . "' />";
    }
    
    //校验
    private function checkSignature(){
        // you must define TOKEN by yourself
        if (!defined("TOKEN")) {
            throw new Exception('TOKEN is not defined!');
        }
        
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        
        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        Log::write('$tmpStr : ' . $tmpStr);
        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }
    
    //获取微信全局access_token（普通access_token）
    public function getWxAccessToken(){
        //将access_token存在session/cookie中
//         $_SESSION['access_token'] = '';
        if($_SESSION['access_token'] && $_SESSION['expire_time'] > time()){
            //如果access_token在session中并且没有过期
            return $_SESSION['access_token'];
        }else{
            
            //1.请求URL地址
            $appId = APPID;
            $appsecret = APPSECRET;
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $appId . '&secret=' . $appsecret;
            //4.调用接口
            $res = $this->http_curl($url,'get','json');
            
            $_SESSION['access_token'] = $res['access_token'];
            $_SESSION['expire_time'] = time() + $res['expires_in'];
            
            return $res['access_token'];
        }
        
        
        //7RYRZ3PS-5m7RrSlWN4pRLqIVw0aVy-4ggOebDw38g7J3hK3rgnrh3JErPcpqKADCzER3R-3C8iBbIob9uxhyDWkg_jaZkeDtPzExTUpLuyp8y_ridClhnHiALhf1bRvQVXdAGAJOQ
    }
    
    //获取微信服务器IP地址列表
    public function getWxServerIP(){
        $accessToken = $this->getWxAccessToken();
        //1.URL
        $url = 'https://api.weixin.qq.com/cgi-bin/getcallbackip?access_token=' . $accessToken;
        //2.初始化curl
        $ch = curl_init();
        //3.设置参数
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        //调用接口
        $res = curl_exec($ch);
        //关闭url
        curl_close($ch);
        
        //如果有错误，打印错误信息
        if(curl_errno($ch)){
            var_dump(curl_error($ch));
        }
        $arr = json_decode($res,true);
        var_dump($arr);
        
    }
    
    //网页授权之静默授权（snsapi_base）
    public function getBaseInfo(){
        //1.获取code
        $appId = APPID;
        $redirectUri = urlencode('http://www.hnxuanju.com/index.php/Home/Weixin/getUserOpenId');//对url进行编码
        $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $appId . '&redirect_uri=' . $redirectUri . '&response_type=code&scope=snsapi_base&state=123#wechat_redirect';
        header("location:" . $url);
    }
    
    //获取用户openid
    public function getUserOpenId(){
        //2.获取网页授权access_token
        $code = $_GET['code'];
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . APPID . '&secret=' . APPSECRET . '&code=' . $code . '&grant_type=authorization_code';
        //3.拉取用户openid
        $res = $this->http_curl($url, 'get');
        
        var_dump($res);
    }
    
    //网页授权之用户确认授权(snsapi_userinfo)
    public function getUserDetail(){
        //1.获取code
        $appId = APPID;
        $redirectUri = urlencode('http://www.hnxuanju.com/index.php/Home/Weixin/getUserInfo');//对url进行编码
        $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $appId . '&redirect_uri=' . $redirectUri . '&response_type=code&scope=snsapi_userinfo&state=123#wechat_redirect';
        header("location:" . $url);
    }
    
    //获取用户详细信息
    public function getUserInfo(){
        //2.获取网页授权access_token
        $code = $_GET['code'];
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . APPID . '&secret=' . APPSECRET . '&code=' . $code . '&grant_type=authorization_code';
        $res = $this->http_curl($url, 'get');
        $openid = $res['openid'];
        $accessToken = $res['access_token'];
        //3.拉取用户openid
        
        $url = 'https://api.weixin.qq.com/sns/userinfo?access_token=' . $accessToken . '&openid=' . $openid . '&lang=zh_CN';
        $res = $this->http_curl($url);
        var_dump($res);
    }
    
    
    /**
     * 通用http请求
     * @param $url         请求URL string
     * @param $type        请求类型(默认:get) string
     * @param $res         返回数据类型(默认:json) string
     * @param $postJson    请求参数 string
     */
    public function http_curl($url,$type = 'get',$res = 'json',$postJson = '') {
        //获取imooc
        //1.初始化curl
        $ch = curl_init();
        //2.设置curl的参数
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        if($type == 'post'){
            curl_setopt($ch,CURLOPT_POST,1);
            curl_setopt($ch,CURLOPT_POSTFIELDS,$postJson);
        }
        //3.采集
        $output = curl_exec($ch);
        //4.关闭
        curl_close($ch);
        
        if($res == 'json'){
            if(curl_errno($ch)){
                //请求失败，返回错误信息
                return curl_error($ch);
            }else{
                return json_decode($output,true);
            }
        }else{
            return $output;
        }
        
    }
    
    //创建微信菜单
    public function createWxMenu(){
        //目前微信接口调用方式通过 crul post/get
        header('content-type:text/html;charset=utf-8');
        $accessToken = $this->getWxAccessToken();
        echo $accessToken;
        echo '<hr />';
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token=' . $accessToken;
        $arr = array(
            'button' => array(
                //第一个一级菜单
                array(
                    'name'=>urlencode('菜单一'),
                    'type'=>'click',
                    'key'=>'item1'
                ),
                //第二个一级菜单
                array(
                    'name'=>urlencode('菜单二'),
                    'sub_button'=>array(
                        array(
                            "name"=>urlencode("今日歌曲"),
                            "type"=>"click",
                            "key"=>"V1001_TODAY_MUSIC"
                        ),
                        array(
                            'name'=>urlencode('电影'),
                            'type'=>'view',
                            'url'=>'http://www.iqiyi.com'
                        )
                    )
                ),
                array(
                    'name'=>urlencode('菜单三'),
                    'type'=>'view',
                    'url'=>'http://www.qq.com'
                ),
                /* array(
                    'name'=>urlencode('玄驹科技'),
                    'sub_button'=>array(
                        array(
                            "name"=>urlencode("官网"),
                            "type"=>"view",
                            "url"=>"http://www.blackcolt.cn"
                        ),
                        array(
                            "name"=>urlencode("客户案例"),
                            "type"=>"view",
                            "url"=>"http://www.blackcolt.cn/cases"
                        ),
                        array(
                            'name'=>urlencode('关于我们'),
                            'type'=>'view',
                            'url'=>'http://www.blackcolt.cn/abouts/lj'
                        )
                    )
                ),
                array(
                    'name'=>urlencode('联系我们'),
                    'type'=>'view',
                    'url'=>'http://www.blackcolt.cn/contacts'
                ), */
            ),
        );
        echo json_encode($arr);
        echo '<hr />';
        
        $postJson = urldecode(json_encode($arr));
        echo $postJson;
        echo '<hr />';
        $res = $this->http_curl($url,'post','json',$postJson);
        var_dump($res);
        
    }
    
    //获取jsapi-ticket票据
    public function getJsApiTicket(){
        //如果session中有有效的ticket
        if($_SESSION['jsapi_ticket'] && $_SESSION['jsapi_ticket_expire_time'] > time()){
            $ticket = $_SESSION['jsapi_ticket'];
        }else{
            $accessToken = $this->getWxAccessToken();
            $url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=' . $accessToken . '&type=jsapi';
            $res = $this->http_curl($url);
            
            $ticket = $res['ticket'];
            
            $_SESSION['jsapi_ticket'] = $ticket; 
            $_SESSION['jsapi_ticket_expire_time'] = time() + 7000; 
        }
        return $ticket;
    }
    
    //获取随机码
    public function getRandCode($num=16){
        $arr = array(
          'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z', 
          'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z', 
          '0','1','2','3','4','5','6','7','8','9'
        );
        $temStr = '';
        $max = count($arr);
        for($i = 1; $i <= $num; $i++){
            $key = rand(0,$max - 1);
            $temStr .= $arr[$key];
        }
        return $temStr;
    }
    
    //jsapi-jdk 微信分享
    public function shareWx(){
        //1.活动jsapi-ticket票据
        $ticket = $this->getJsApiTicket();
        $timestamp = time();
        $nonceStr = $this->getRandCode();
        $url = 'http://www.hnxuanju.com/index.php/Home/Weixin/shareWx';
        //2.获取签名算法
        $signature = 'jsapi_ticket=' . $ticket . '&noncestr=' . $nonceStr . '&timestamp=' . $timestamp . '&url=' . $url;
        $signature = sha1($signature);
        $this->assign('timestamp',$timestamp);
        $this->assign('nonceStr',$nonceStr);
        $this->assign('signature',$signature);
        $this->display('share');
    }
    
    //群发预览
    public function sendPreviewMassMsg(){
        $accessToken = $this->getWxAccessToken();
        $url = 'https://api.weixin.qq.com/cgi-bin/message/mass/preview?access_token=' . $accessToken;
        
        
        
        $type = 'image';
        if('text' == $type){
//             { "touser":"OPENID", "text":{ "content":"CONTENT" }, "msgtype":"text" }
            $arr = array(
                'touser'=>'oLOObsxeGDRw-lNIEE6otZxOrP9E',
                'msgtype'=>$type,
                'text'=>array(
                    'content'=>urlencode('群发文本预览接口。')
                )
            );
        }else if('image' == $type){
            //{ "touser":"OPENID", "image":{ "media_id":"123dsdajkasd231jhksad" }, "msgtype":"image" }
            $arr = array(
                'touser'=>'oLOObsxeGDRw-lNIEE6otZxOrP9E',
                'msgtype'=>$type,
                'image'=>array(
                    'media_id'=>'CeWH7ITIeivdn6E3pTBoLOUXRP_Ruk3VHyIiBlrYb-Lrn2X_i8QM1hUSTN6cFjFv'
                )
            );
        }
        
        $postJson = urldecode(json_encode($arr));
        $res = $this->http_curl($url,'post','json',$postJson);
        echo date('Y-m-d H:i:s',time()) . " $type 群发预览发送结果：";
        echo '<hr />';
        var_dump( $res);
    }
    
    //群发
    public function sendMassMsg(){
        $accessToken = $this->getWxAccessToken();
        $url = 'https://api.weixin.qq.com/cgi-bin/message/mass/preview?access_token=' . $accessToken;
        
        
        
        $type = 'image';
        if('text' == $type){
//             { "touser":"OPENID", "text":{ "content":"CONTENT" }, "msgtype":"text" }
            $arr = array(
                'touser'=>'oLOObsxeGDRw-lNIEE6otZxOrP9E',
                'msgtype'=>$type,
                'text'=>array(
                    'content'=>urlencode('群发文本接口。')
                )
            );
        }else if('image' == $type){
            //{ "touser":"OPENID", "image":{ "media_id":"123dsdajkasd231jhksad" }, "msgtype":"image" }
            $arr = array(
                'touser'=>'oLOObsxeGDRw-lNIEE6otZxOrP9E',
                'msgtype'=>$type,
                'image'=>array(
                    'media_id'=>'CeWH7ITIeivdn6E3pTBoLOUXRP_Ruk3VHyIiBlrYb-Lrn2X_i8QM1hUSTN6cFjFv'
                )
            );
        }
        
        $postJson = urldecode(json_encode($arr));
        $res = $this->http_curl($url,'post','json',$postJson);
        echo date('Y-m-d H:i:s',time()) . " $type 群发送结果：";
        echo '<hr />';
        var_dump( $res);
    }
    
    //上传临时素材文件
    public function tempMediaUpload(){
        $accessToken = $this->getWxAccessToken();
        $type = 'image';
        $url = 'https://api.weixin.qq.com/cgi-bin/media/upload?access_token=' . $accessToken . '&type=' . $type;
//         $imgUrl = 'http://www.iyi8.com/uploadfile/2016/0424/20160424113910552.jpg';
        $imgUrl = 'D:\3.jpg';
        $arr = array(
            'media'=>'@'.$imgUrl
        );
        
        $postJson = json_encode($arr);
        $res = $this->http_curl($url,'post','json',$postJson);
        echo date('Y-m-d H:i:s',time()) . '临时输出上传 ' . $type;
        echo '<hr />';
        var_dump($res);
    }
    
    public function test(){
        //echo phpinfo();
        
        $url = 'http://www.jb51.net';
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . APPID . "&secret=" . APPSECRET;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch); // 已经获取到内容，没有输出到页面上。
        curl_close($ch);
        echo $response;
    }
}