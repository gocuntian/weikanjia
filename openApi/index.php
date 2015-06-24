<?php
/**
 * controller: 用户第一次打开提示权限
 * create by sixian
 * @2015-05-04
 * ------------------
 */

header("Content-Type: text/html;charset=utf-8");

$wxpayapi = getUrl();
$wxpayapi .= "loading.php";

$wxpayapiv42 = getUrl();
$wxpayapiv42 .= "loadingv42.php";

$notify_url = getUrl();
$notify_url .= "wxnotify.php";

$wxauth = getUrl();
$wxauth .= "wxauth.php";

$incPaths = dirname(__FILE__);
if(file_exists("{$incPaths}/WxPayPubHelper/WxPay.pub.config.php")){ 
	echo "<li>WxPayPubHelper/WxPay.pub.config.php配置文件已生成</li>
	<br><li>要想重新配置请先删除此文件再重新配置！</li>
	<br><li>请将此程序放到 网页微信授权域名 目录！</li>
	<br/><li>微砍价微信支付api接口为: <span style='color:red'>$wxpayapi</span></li>
	<br/><li>v42微信接口为: <span style='color:red'>$wxpayapiv42</span></li>	
	
	<br/><li>微砍价微信授权调接口为: <span style='color:red'>$wxauth</span></li>	


	<br/>
<b>注意在微信公众平台后台设置以上 支付目录！！！</b>
<br/>
<h3>目录结构：</h3>
	";
	$readme = $f = file_get_contents ("readme.txt");
	echo htmlspecialchars_decode($readme);
	exit;
}

$msg = "";

$upload_file = dirname(__FILE__) .'/upload'; 
$WxPayPubHelper_file = dirname(__FILE__) .'/WxPayPubHelper'; 
$log_file = dirname(__FILE__) .'/log'; 

if ( !is_writable($upload_file) ) { 
	$msg .= "<br/>upload/ 目录无写入权限！";
}

if ( !is_writable($WxPayPubHelper_file) ) { 
	$msg .= "<br/>WxPayPubHelper/ 目录无写入权限！<br/>";
}

if ( !is_writable($log_file) ) { 
	$msg .= "<br/>log/ 目录无写入权限！<br/>";
}

echo $msg;

if ( empty($msg) ){
	header("location:install.php");
	exit;
}



// 得到层级目录
function getUrl($replace = '') {
	$currentUrl = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
	$urlArr = parse_url($currentUrl);
	$newPath = preg_replace('/\/[^\/]+\.php$/i', "/{$replace}", $urlArr['path']);

	$queryString = isset($urlArr['query']) ? $urlArr['query'] : '';

	$linkUrl = $newPath . (!empty($queryString) ? "?{$queryString}" : '');
	return 'http://'.$_SERVER['HTTP_HOST'].$linkUrl;
}
?>
