<?php

/**
 * controller: 配置文件输入表单
 * create by sixian
 * @2015-05-04
 * ------------------
 */

header("Content-Type: text/html;charset=utf-8");


$js_api_cll_url = getUrl();
$js_api_cll_url .= "wxpayapi.php?showwxpaytitle=1";

$notify_url = getUrl();
$notify_url .= "wxnotify.php";

$ideayun_api = getUrl();
$ideayun_api = preg_replace('/openApi/', "ideaApi", $ideayun_api);
$ideayun_api .= "open_api.php";
$ideayun_api  = "http://auth.xinyuemin.net/customers/ideaApi2/open_api_client.php";

$incPaths = dirname(__FILE__);
if(file_exists("{$incPaths}/WxPayPubHelper/WxPay.pub.config.php")){ 
	if (isset($_GET['del'])){
		$result = @unlink ("{$incPaths}/WxPayPubHelper/WxPay.pub.config.php"); 
		header("location:install.php");
		exit;
	}

	echo "WxPayPubHelper/WxPay.pub.config.php配置文件已存在请先删除此文件再<a href='install.php?del=true'>重新配置！</a>";
	exit;
}

// 保存证书 1
if (isset($_FILES["SSLCERT_PATH"]) && $_FILES["SSLCERT_PATH"]['type'] == 'application/octet-stream')
 {
	move_uploaded_file($_FILES["SSLCERT_PATH"]["tmp_name"],
	"upload/" . $_FILES["SSLCERT_PATH"]["name"]);
	$SSLCERT_PATH = $incPaths . '/upload/'.$_FILES["SSLCERT_PATH"]["name"];
 }
// 保存证书 2
if (isset($_FILES["SSLKEY_PATH"]) && $_FILES["SSLKEY_PATH"]['type'] == 'application/octet-stream')
 {
	move_uploaded_file($_FILES["SSLKEY_PATH"]["tmp_name"],
	"upload/" . $_FILES["SSLKEY_PATH"]["name"]);
	$SSLKEY_PATH = $incPaths . '/upload/'.$_FILES["SSLKEY_PATH"]["name"];	
 }


if (isset($_GET)){
	$APPID = isset($_POST['APPID']) ? htmlspecialchars(trim($_POST['APPID'])) : '';
	$MCHID = isset($_POST['MCHID']) ? htmlspecialchars(trim($_POST['MCHID'])) : '';
	$KEY = isset($_POST['KEY']) ? htmlspecialchars(trim($_POST['KEY'])) : '';
	$APPSECRET = isset($_POST['APPSECRET']) ? htmlspecialchars(trim($_POST['APPSECRET'])) : '';
	$JS_API_CALL_URL = isset($_POST['JS_API_CALL_URL']) ? htmlspecialchars(trim($_POST['JS_API_CALL_URL'])) : '';
	$SSLCERT_PATH = @$SSLCERT_PATH;
	$SSLKEY_PATH = @$SSLKEY_PATH;
	$NOTIFY_URL = isset($_POST['NOTIFY_URL']) ? htmlspecialchars(trim($_POST['NOTIFY_URL'])) : '';

	$SID             = isset($_POST['SID']) ? htmlspecialchars(trim($_POST['SID'])) : '';
	$privateKey      = isset($_POST['privateKey']) ? htmlspecialchars(trim($_POST['privateKey'])) : '';
	$sitename        = isset($_POST['sitename']) ? htmlspecialchars(trim($_POST['sitename'])) : '';
	$updateApiUrl    = isset($_POST['updateApiUrl']) ? htmlspecialchars(trim($_POST['updateApiUrl'])) : '';		

	$OAUTH_HOST      = isset($_POST['OAUTH_HOST']) ? htmlspecialchars(trim($_POST['OAUTH_HOST'])) : '';		

	$date   = date('Y-m-d H:i:s');
	$config = <<<eof
<?php
/**
* 	配置账号信息 $date
* 	自动生成的配置文件 
*/

class WxPayConf_pub
{
	//=======【基本信息设置】=====================================
	//微信公众号身份的唯一标识。审核通过后，在微信发送的邮件中查看
	const APPID = "$APPID";
	//受理商ID，身份标识
	const MCHID = "$MCHID";
	//商户支付密钥Key。审核通过后，在微信发送的邮件中查看
	//const KEY = 'Xinyuemin20140930Tan20140508dfhH';
	const KEY = "$KEY";
	//JSAPI接口中获取openid，审核后在公众平台开启开发模式后可查看
	const APPSECRET = "$APPSECRET";
	
	//=======【JSAPI路径设置】===================================
	//获取access_token过程中的跳转uri，通过跳转将code传入jsapi支付页面
	const JS_API_CALL_URL = "$JS_API_CALL_URL";
	
	//=======【证书路径设置】=====================================
	//证书路径,注意应该填写绝对路径
	const SSLCERT_PATH =  "$SSLCERT_PATH";	
	const SSLKEY_PATH =   "$SSLKEY_PATH";
	
	//=======【异步通知url设置】===================================
	//异步通知url，商户根据实际开发过程设定
	const NOTIFY_URL = "$NOTIFY_URL";

	//=======【curl超时设置】===================================
	//本例程通过curl使用HTTP POST方法，此处可修改其超时时间，默认为30秒
	const CURL_TIMEOUT = 30;

	//=======【idea云平台的网站id】===================================
	// 在云平台的网站id
	const SID = "$SID";	

	//=======【idea云平台签名密钥】===================================
	//在云平台 配置的 签名密钥
	const privateKey = "$privateKey";

	//=======【此支付网站名称】===================================
	// 此支付网站名称
	const sitename = "$sitename";

	//=======【idea开放平台 支付流水接口】===================================
	// 云idea开放平台 支付流水接口
	const updateApiUrl = "$updateApiUrl";

	//=======【微信授权接口调用域名白名单】===================================
	// 【微信授权接口调用域名白名单】
	const OAUTH_HOST = "$OAUTH_HOST";	

}

?>
eof;
// 写入文件
	if (!empty($APPID) && !empty($NOTIFY_URL)){
		writefile("{$incPaths}/WxPayPubHelper/WxPay.pub.config.php",$config);
		header("location:index.php");
		exit;
	}
}

// 写入文件公用方法
function writefile($fname,$str){
	$fp=fopen($fname,"w");
	fputs($fp,$str);
	fclose($fp);
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
<!DOCTYPE html>
<html>
	<head>
		<link href="http://cdn.bootcss.com/bootstrap/3.3.4/css/bootstrap.min.css" rel="stylesheet">
	</head>
	<body>
		<div class="col-xs-7" style="  padding: 37px;">
			<form class="form-horizontal" action="./install.php" method="POST" enctype = 'multipart/form-data' >


				<div class="form-group">
					<label for="exampleInputEmail1">APPID</label>
					<input type="text" class="form-control"  name ="APPID" placeholder="微信公众号身份的唯一标识。审核通过后，在微信发送的邮件中查看">
				</div>

				<div class="form-group">
					<label for="exampleInputEmail1">APPSECRET</label>
					<input type="text" class="form-control"  name ="APPSECRET" placeholder="JSAPI接口中获取openid，审核后在公众平台开启开发模式后可查看">
				</div>

				<div class="form-group">
					<label for="exampleInputEmail1">MCHID</label>
					<input type="text" class="form-control"  name ="MCHID" placeholder="受理商ID，身份标识">
				</div>

				<div class="form-group">
					<label for="exampleInputEmail1">KEY</label>
					<input type="text" class="form-control"  name ="KEY" placeholder="商户支付密钥Key。审核通过后，在微信发送的邮件中查看">
				</div>

				<div class="form-group">
					<label for="exampleInputEmail1">SSLCERT_PATH <span style="color:red">apiclient_cert.pem</span></label>
					<input type="file" class="form-control"  name ="SSLCERT_PATH" id="SSLCERT_PATH" placeholder="证书路径,注意应该填写绝对路径  apiclient_cert.pem">
				</div>

				<div class="form-group">
					<label for="exampleInputEmail1">SSLKEY_PATH <span style="color:red">apiclient_key.pem</span></label>
					<input type="file" class="form-control"  name ="SSLKEY_PATH" id="SSLKEY_PATH"  placeholder="证书路径,注意应该填写绝对路径 apiclient_key.pem">
				</div>

				<div class="form-group">
					<label for="exampleInputEmail1">JS_API_CALL_URL(获取access_token过程中的跳转uri，通过跳转将code传入jsapi支付页面)</label>
					<input type="text" class="form-control"  name ="JS_API_CALL_URL" placeholder="获取access_token过程中的跳转uri，通过跳转将code传入jsapi支付页面" value="<?php echo $js_api_cll_url;?>">
				</div>
				<div class="form-group">
					<label for="exampleInputEmail1">NOTIFY_URL(异步通知url，商户根据实际开发过程设定)</label>
					<input type="text" class="form-control"  name ="NOTIFY_URL" placeholder="异步通知url，商户根据实际开发过程设定" value="<?php echo $notify_url;?>">
				</div>	

				<div class="form-group">
					<label for="exampleInputEmail1">idea云平台网站id</label>
					<input type="text" class="form-control"  name ="SID" placeholder="idea云平台网站id" value="">
				</div>		

				<div class="form-group">
					<label for="exampleInputEmail1">idea云平台签名密钥</label>
					<input type="text" class="form-control"  name ="privateKey" placeholder="idea云平台支付签名">
				</div>	

				<div class="form-group">
					<label for="exampleInputEmail1">网站名称</label>
					<input type="text" class="form-control"  name ="sitename" placeholder="网站名称">
				</div>	

				<div class="form-group">
					<label for="exampleInputEmail1">idea开放平台 支付流水接口</label>
					<input type="text" class="form-control"  name ="updateApiUrl" placeholder="idea开放平台 支付流水接口" value="<?php echo $ideayun_api;?>">
				</div>	

				<div class="form-group">
					<label for="exampleInputEmail1">微信授权接口调用域名白名单</label>
					<input type="text" class="form-control"  name ="OAUTH_HOST" placeholder="微信授权接口调用域名白名单 多个域名用 | 分开" value="|xinyuemin.com|idea0086.com|v32test.wkj.xinyuemin.com|加上你们微砍价要授权的域名">
				</div>	

				<div class="form-group">
					<button type="submit">开始配置</button>
				</div>


	
			</form>	
		</div>	
	</body>
<script src="http://cdn.bootcss.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>	
</html>