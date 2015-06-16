<?php
/**
 * oauth
 * update by fangwuyi
 * @2014-10-27
 * ------------------
 * ocphp supported @2014-05-10
*/

//start session

//include common files
$incPath = dirname(__FILE__);
include_once("{$incPath}/WxPayPubHelper/WxPayPubHelper.php");

session_start();
/**
 * ------------------
 * url：回传地址；
 * auth：授权模式
 * -- auth1: 回传openid
 * -- auth2: 回传微信资料
 * app：项目名
 */


/**----------------
 * controll logical code here
 * {{{
 */
/**
*需要修改的变量
* $privateKey 签名密钥使用
* $appid , $secret 授权使用
* $hid 设置cookie使用
*/

$privateKey =  WxPayConf_pub::privateKey;

//设置cookie
$hid_key = 'wkj'.  WxPayConf_pub::SID;
$hidurl = $hid_key.'redurl';
$hidauthtype = $hid_key.'authtype';

$dqurl =  IdeaUtil::getweburl('wxauth.php').'wxauth.php';

//保存请求来源的cookie key名
$fromappKey = "wxauth_fromapp_{$hid_key}";

//echo $dqurl;exit;
$openidfw =  isset($_GET['openidfw']) ? $_GET['openidfw'] : '';
$xymtimestamp =  isset($_GET['timestamp']) ? $_GET['timestamp'] : '';
$xymsignature =  isset($_GET['signature']) ? $_GET['signature'] : '';
$xymnonce =  isset($_GET['nonce']) ? $_GET['nonce'] : '';
$xymkey = '515xinyuemin';
if( !empty($openidfw) || isset($_GET['errcode']) ){//如果参数中带了openidfw 或者errcode 则判断为新粤闽授权页返回

	if( IdeaUtil::generateSignature($xymsignature,$xymtimestamp,$xymnonce,$xymkey) ){
		$_SESSION["xymopenid"] = $openidfw;
	}else{//签名失败
		$_SESSION["xymerrcode"]= isset($_GET['errcode']) ? isset($_GET['errcode']) : 'xymsignerror';
	}
	$redurl = $_SESSION["$hidurl"];//取出回传网址
	$_REQUEST['auth'] = $_SESSION["$hidauthtype"];//取出授权模式
	//print_r($_REQUEST['auth']);exit;
}else{

	//回传网址保存和模式
	$redurl =  isset($_GET['url']) ? $_GET['url'] : '';
    $_SESSION["$hidurl"] = $redurl;
    $_SESSION["$hidauthtype"] = isset($_GET['auth']) ? $_GET['auth'] : '';
    
    
    //请求app保存
    $fromApp =  isset($_GET['app']) ? $_GET['app'] : '';
    if (empty($fromApp)) {  //use host
        $fromApp = IdeaUtil::getHostFromReferer($redurl);
        //error_log("fromApp: {$fromApp}\n\n", 3, '/var/log/debug.log');
    }
    if(!empty($fromApp)){
        setcookie($fromappKey, $fromApp, time()+3600*24, '/');
    }else {
        setcookie($fromappKey, '', time()-3600*24, '/');
    }

    if( !empty($redurl) ){
   		//去新粤闽授权
		$xymurl = 'http://xinyuemin.net/wechat/?auth=auth1&url='.$dqurl;
		header("location: $xymurl");exit;
    }
    
}



//增加 ？或者 & 以便后面增加参数
if(!empty($redurl)){		
    if(stristr($redurl,"?")){
        $redurl = $redurl.'&';
    }else{
        $redurl = $redurl.'?';
    }

    //添加新粤闽授权回来的参数
	if( isset($_SESSION["xymopenid"]) ){
		$redurl = $redurl.'xymopenid='.$_SESSION["xymopenid"].'&';
	}
	if( isset($_SESSION["xymerrcode"]) ){
		$redurl = $redurl.'xymerrcode='.$_SESSION["xymerrcode"].'&';
	}

    setcookie("$hidurl",$redurl,time()+3600*24,'/');
    $_SESSION["$hidurl"] = $redurl;
}

$allow = explode('|', WxPayConf_pub::OAUTH_HOST);


//增加 ？或者 & 以便后面增加参数
if(!empty($redurl)){
	$allow_ok = "";
	$urlarr = parse_url($redurl);
	$redhost = $urlarr['host'];	
	if(!in_array($redhost, $allow)){

		print_r($redhost);
		echo "<br/>";
		print_r($allow);
		echo "host not access!";
		exit;
	}
}

//appid
$appid =  WxPayConf_pub::APPID;
$secret =  WxPayConf_pub::APPSECRET;

//网页授权
if(isset($_REQUEST['auth']) && !empty($_REQUEST['auth'])){
	$authtype = $_REQUEST['auth'];
	
	if($authtype == "auth1"){//类型1：只获取openid；

		$code = isset($_REQUEST['code']) ? $_REQUEST['code'] : '';
		if($code == ""){
			//$url_this = "http://".$_SERVER ['HTTP_HOST'].$_SERVER['PHP_SELF'];
			//$url1 = $url_this.'?auth=auth1';
			$url_this = "http://".$_SERVER ['HTTP_HOST'].$_SERVER['REQUEST_URI'];
			if(strstr($url_this, "?")){
				$wurl = $url_this.'&';
			}else{
				$wurl = $url_this.'?';
			}
			$wurl = urlencode($wurl);
			$wxurl = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=$appid&redirect_uri=".$wurl."response_type=code&scope=snsapi_base&state=STATE#wechat_redirect";
			header("Location: $wxurl");exit;
		}else{
			$code = $_REQUEST['code'];
			$tourl = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$secret&code=$code&grant_type=authorization_code";
			$result = IdeaUtil::curlGet($tourl);
			$rst = json_decode($result,true);//var_dump($rst);exit;
			$url_this = "http://".$_SERVER ['HTTP_HOST'].$_SERVER['REQUEST_URI'];
			$backurl = isset($_COOKIE["$hidurl"]) ? $_COOKIE["$hidurl"] : $url_this;

			$access_token = isset($rst["access_token"]) ? $rst["access_token"] : '';
			$openid = isset($rst["openid"]) ? $rst["openid"] : '';
			$errcode = isset($rst["errcode"]) ? $rst["errcode"] : '';
			if(!empty($errcode)){
				$durl = $_COOKIE["$hidurl"]."errcode=$errcode";
			  	header("location: $durl");exit;
			}
			// echo $openid; // 返回数据
			$durl = $_COOKIE["$hidurl"]."openidfw=$openid";
			//echo $durl.'<br>';
			/* header("location: $durl");exit; */

			//增加签名
			$timestamp = time();
			$nonce = md5( base64_encode($openid.$timestamp.'wechat') );
			//$privateKey = '515xinyuemin'; //echo $privateKey ;exit;
			$signature = IdeaUtil::generateSignature($timestamp , $nonce , $privateKey);
			$signurl = $durl.'&timestamp='.$timestamp.'&signature='.$signature.'&nonce='.$nonce;
			//echo $signurl;exit();
			header("location: $signurl");exit;
		}
	}else if($authtype == "auth2" || $authtype == "auth3"){//类型2：获取微信全部信息。
		$code = isset($_REQUEST['code']) ? $_REQUEST['code'] : '';
		if($code == ""){
			$url_this = "http://".$_SERVER ['HTTP_HOST'].$_SERVER['REQUEST_URI'];
			if(strstr($url_this, "?")){
				$wurl = $url_this.'&';
			}else{
				$wurl = $url_this.'?';
			}
			//echo $wurl;exit;
			$wurl = urlencode($wurl);
			$wxurl = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=$appid&redirect_uri=".$wurl."&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect";
			header("Location: $wxurl");exit;
		}else{
			$code = $_REQUEST['code'];
			$tourl = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$secret&code=$code&grant_type=authorization_code";
			$result = IdeaUtil::curlGet($tourl);
			$rst = json_decode($result,true);
			//var_dump($rst);exit;
			$errcode = isset($rst["errcode"]) ? $rst["errcode"] : '';
			$errmsg = isset($rst["errmsg"]) ? $rst["errmsg"] : '';
			if(!empty($errcode )){
			  	$durl = $_COOKIE["$hidurl"]."errcode=$errcode";
			  	header("location: $durl");exit;
				//echo $errcode; exit; // 返回数据
				
			}
			$access_token=$rst["access_token"];
			$refresh_token=$rst["refresh_token"];
			$openid=$rst["openid"];
			$scope=$rst["scope"];
			
	        //获取用户信息
			$aaurl="https://api.weixin.qq.com/sns/userinfo?access_token=$access_token&openid=$openid&lang=zh_CN";
			$users = IdeaUtil::curlGet($aaurl);
			//$user=json_decode($users,true);
			//var_dump($user);exit;				
			//echo($users); // 返回数据
			
			//增加xymopenid字段
			$originalWxuser = json_decode($users, true);
            $originalWxuser['xymopenid'] = $_SESSION["xymopenid"];
            $users = json_encode($originalWxuser);


			$users = urlencode($users);
			$openidfw = $openid;
			$durl = $_COOKIE["$hidurl"]."openidfw=$openidfw&wxuser=$users";
			//echo $durl;exit;
			/* header("location: $durl");exit; */

            
            //向云平台传微信用户数据库
            /*$yunWxuserApi = 'http://yun.xinyuemin.com/api/user/';
            $yunWxData = array(
                'act' => 'addwxuser',
                'sn' => 'Xinyuemin515',
                'wxuser' => $users,
                'appid' => (isset($_COOKIE[$fromappKey]) ? $_COOKIE[$fromappKey] : 'wechatauth'),
            );
            Util::curlGet($yunWxuserApi, 'post', $yunWxData, 1);*/
            //error_log("Got wxuser in auth: " . json_encode($yunWxData) . "\n", 3, '/var/log/debug.log');
            
            
			 //增加签名
			$timestamp = time();
			$nonce = md5( base64_encode($openid.$timestamp.'wechat') );
			//$privateKey = '515xinyuemin';
			$signature = IdeaUtil::generateSignature($timestamp , $nonce , $privateKey);
			$signurl = $durl.'&timestamp='.$timestamp.'&signature='.$signature.'&nonce='.$nonce;

			header("location: $signurl");exit;
		}
	}

}


exit;



/**----------------
 * }}}
 */
