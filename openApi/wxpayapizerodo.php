<?php
/**
 * controller: 微砍价通用支付接口 0元支付提交接口
 * create by sixian
 * @2015-05-05
 * ------------------
 */


//start session
session_start();

/**----------------
 * include common files
 */

$incPaths = dirname(__FILE__);
require_once "{$incPaths}/WxPayPubHelper/WxPayPubHelper.php";


/**----------------
 * controll logical code here
 * {{{
 */

/**
 *接收参数
 *验证签名，
 *显示订单确认页
 *确认支付处理
 *保存支付流水数据
 *返回数据、状态
*/


if( !isset($_SESSION['oid']) || !isset($_SESSION['apiurl']) | empty($_SESSION['oid']) || empty($_SESSION['apiurl']) )
{	
	echo '{"errcode":"011","errmsg":"session error!"}';exit;
}else if( !empty($_SESSION['oid']) && !empty($_SESSION['apiurl']) ){

	$pageData['oid'] = $_SESSION['oid'];
	$pageData['apiurl'] = $_SESSION['apiurl'];
	$api['oid'] = $pageData['oid']; 
	$apidatas = IdeaUtil::curlGet($pageData['apiurl'],'post',$api); //从api读取数据
	$apirst = json_decode($apidatas,true);
	$datas = $apirst['data'];

	$pageData['backurl'] = !empty($datas['backurl']) ? $datas['backurl'] : 'http://xinyuemin.com';
	if(stristr($pageData['backurl'],"?")){
		$pageData['backurl'] = $pageData['backurl'].'&';
	}else{
		$pageData['backurl'] = $pageData['backurl'].'?';
	}
	$pageData['prouct_pic'] = $datas['prouct_pic'];
	$pageData['prouct_title'] = $datas['prouct_title'];
	$pageData['prouct_introduction'] = $datas['prouct_introduction'];
	$pageData['prouct_price'] = $datas['prouct_price'];
	$pageData['prouct_pay'] = $datas['prouct_pay'];
	$pageData['siteid'] = $datas['siteid'];
	$pageData['updateapi'] = $datas['updateapi'];
	$pageData['oid'] = $datas['orderid'];//原始订单号
}

//从商户订单号中获取网站ID，从而获取该网站的动态密钥
$sid = IdeaUtil::getSidFromOut_trade_no($pageData['oid']);
// 读取签名密钥 网址名
$privateKey = WxPayConf_pub::privateKey;
$sitename   = WxPayConf_pub::sitename;

//支付处理
$action = isset($_REQUEST['aciton']) ? $_REQUEST['aciton'] : '';
$orderid = isset($_REQUEST['orderid']) ? $_REQUEST['orderid'] : 'test';
$openid = isset($_REQUEST['openid']) ? $_REQUEST['openid'] : '';

$backUrl = "{$pageData['backurl']}oid={$pageData['oid']}&wxorderid={$orderid}&openid={$openid}&paystatus=";
$paystatus = 'get_brand_wcpay_request:fail';

if( $action=='wxpayzero' ){

	$timestamp = isset($_REQUEST['timestamp']) ? $_REQUEST['timestamp'] : '';
	$sign = isset($_REQUEST['sign']) ? $_REQUEST['sign'] : 'ok';
	$oid = isset($_REQUEST['oid']) ? $_REQUEST['oid'] : '';
	$apiurl = isset($_REQUEST['apiurl']) ? $_REQUEST['apiurl'] : '';

	//检测参数是否合法
	if( empty($timestamp) || ($timestamp > time()+60*5) ){
		$pageData['error'] = '{"errcode":"001","errmsg":"time out!"}';
	}

	if( empty($sign) ){
		$pageData['error'] = '{"errcode":"002","errmsg":"sign failed!"}';
	}

	if( empty($oid) || $oid!= $_SESSION['oid'] ){
		$pageData['error'] = '{"errcode":"003,"errmsg":"oid error!"}';
	}

	if( empty($apiurl) ||  $apiurl != $_SESSION['apiurl'] ){
		$pageData['error'] = '{"errcode":"004,"errmsg":"data error!"}';
	}

	if( empty($openid) ||  $openid != $_SESSION['openid'] ){
		$pageData['error'] = '{"errcode":"005,"errmsg":"data error!"}';
	}

	if( empty($orderid) ||  $orderid != $_SESSION['orderid'] ){
		$pageData['error'] = '{"errcode":"006,"errmsg":"data error!"}';
	}

	if( isset($pageData['error']) && !empty($pageData['error']) ){
		$url = $backUrl.$paystatus;
		header("Location: {$url}");
		exit();
	}

	//生成签名
	$privateKey = isset($privateKey) ? $privateKey : '515xinyuemin'; //获取不到密钥，就用默认密钥
	$timestamp = time();
	$nonce = md5( base64_encode($openid.$timestamp.$oid.$privateKey) );
	$signature = IdeaUtil::generateSignature($timestamp,$nonce,$privateKey);
	//
	$up_url = $pageData['updateapi'];
	if(stristr($up_url,"?")){
		$up_url = $up_url.'&';
	}else{
		$up_url = $up_url.'?';
	}
	//请求api更新订单状态
	$up_url = $up_url.'signature='.$signature.'&timestamp='.$timestamp.'&nonce='.$nonce;
	$updata['oid'] = $oid;
	$updata['wxpayid'] = $orderid;
	$updata['openid'] = $openid;
	$updata['paytype'] = 'free';
	$up_rest = IdeaUtil::curlGet($up_url,'post',$updata);
	$rst = json_decode($up_rest,true);
	//$paystatus = 'get_brand_wcpay_request:ok';

	if( empty($rst) ){
		$pageData['error'] = '{"errcode":"007,"errmsg":"pay failed!"}';
	}
	if(isset($rst['status']) && $rst['status']==1 ){
		$pageData['error'] =  '{"errcode":"0","errmsg":"ok"}';
	}else{
		$tips['errcode'] = '008';
		$tips['errmsg'] = isset($rst['msg']) ? $rst['msg'] : 'updateapi error!';
		$pageData['error'] =  json_encode($tips);
		$pageData['error'] =  $tips['errmsg'];
	}

	//改变订单流水状态
	$uppaydata['status'] = 2; //0:初始，1:取消，2:付款成功，3:失败，4:订单失效，
	$uppaydata['payway'] = 'free';//weixin：微信预支付，wxpay：微信支付，alipay：支付宝支付
	$uppaydata['time'] = time();
	$uppaydata['wxorderid'] = $orderid;//微信订单号
	$payid = $orderid;//商户微信支付订单号
	$suf = !empty($sid) ? ('_'.$sid) : '';//表后缀
	// $chkpay = $dao_read->chkPayrecordByOrderidHasTabsuf($payid,$suf);
	// 将数据传送到 支付平台
    // 云平台发送数据改为ajax异步{{{
    $postData['openid']  = $openid;
    $postData['sid']     = $sid;
    $postData['oid']     = $pageData['oid'];
    $postData['paydata'] = $uppaydata;

    $postData['action_old']['suf']    = $suf;
    $postData['action_old']['type']   = 'wxpayapizerodo';

    $ajaxUrl             = WxPayConf_pub::updateApiUrl;
    $url = $ajaxUrl.'?'.http_build_query($postData). '&v='. time();
    $apidatas = IdeaUtil::curlGet($url, true,array() );
    // }}}

    
	echo $pageData['error'];
	$res = json_decode($pageData['error'], true);
	if (isset($res['errcode']) && $res['errcode'] == 0){
		$url = $backUrl.$paystatus;
		header("Location: {$url}");		
	}
	exit();
}

exit('*_*');


	
