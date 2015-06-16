<?php
/**
 * controller: 微砍价通用支付接口 0元支付接口
 * create by sixian
 * @2015-05-05
 * ------------------
 */

//start session

/**----------------
 * include common files
 */

$incPaths = dirname(__FILE__);

include_once("{$incPaths}/WxPayPubHelper/WxPayPubHelper.php");

session_start();
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


	$pageData['uid'] = $_SESSION['openid'] = $openid;
	$pageData['wxorderid'] = $_SESSION['orderid'] = $pageData['orderid'];//微信支付商户订单



/**----------------
 * }}}
 */


/**----------------
 * config title, description, keywords
*/
$pageTitle = "零元支付确认-";
$pageTitle .= WxPayConf_pub::sitename;
$pageDescription = '';
$pageKeywords = '';


/**----------------
 * render views
 * layout and views
*/
$themeName = 'weikanjia';    //皮肤目录
$layoutName = 'main';           //布局名称
$viewGroup = 'pay';            //视图目录
$viewName = 'wxpayapizero';             //视图名
?>

<!DocType html>
<html>
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7">
	<meta charset="utf-8">
	<title><?php echo $pageTitle; ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
	<meta name="keywords" content="<?php echo $pageKeywords; ?>">
	<meta name="description" content="<?php echo $pageDescription; ?>">
	<meta content="telephone=no" name="format-detection" />
	<link href="http://cdn.bootcss.com/bootstrap/3.3.4/css/bootstrap.min.css" rel="stylesheet">
	<link href="http://cdn.bootcss.com/bootstrap/3.3.4/css/bootstrap-theme.min.css" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="./theme/css/kanjia.css">
	<script src="http://cdn.bootcss.com/jquery/2.1.4/jquery.min.js"></script>
</head>
<body >

<script type="text/javascript">	
document.addEventListener('WeixinJSBridgeReady', function() {
     WeixinJSBridge.call('hideOptionMenu');
});
</script>
<div style="display:none;" id="paydata" data-backurl="" data-orderid="<?php echo $pageData['oid']; ?>" data-wxorderid="<?php echo $pageData['wxorderid']; ?>" data-uid="<?php echo $pageData['uid']; ?>"></div>
<div class="zh_pay">
	<div class="zh_xinxi clearfix">
		<div class="col-xs-3 quchu"><img src="<?php echo $pageData['prouct_pic']; ?>" /></div>
		<div class="col-xs-9 quchu zh_content">
			<h5><?php echo $pageData['prouct_title']; ?></h5>
			<p><?php echo htmlspecialchars_decode($pageData['prouct_introduction']); ?></p>
		</div>
	</div>
	<div class="zh_jia">
		<p class="zh_yuan">原价：<span>￥<?php echo $pageData['prouct_price']; ?></span></p>
		<p class="zh_shi">实付款：<span>￥<?php echo $pageData['prouct_pay']; ?></span></p>
	</div>
	<form action="wxpayapizerodo.php" method="post">
		<input type="hidden" name="backurl" value="<?php echo $pageData['backurl']; ?>">
		<input type="hidden" name="orderid" value="<?php echo $pageData['orderid']; ?>">
		<input type="hidden" name="apiurl" value="<?php echo $pageData['apiurl']; ?>">
		<input type="hidden" name="oid" value="<?php echo $pageData['oid']; ?>">
		<input type="hidden" name="openid" value="<?php echo $pageData['uid']; ?>">
		<input type="hidden" name="sign" value="<?php echo rand(11,99).time().rand(11,99); ?>">
		<input type="hidden" name="timestamp" value="<?php echo time(); ?>">
		<input type="hidden" name="aciton" value="wxpayzero">
		<button class="zh_zhifu" data-paystatus="1">确认支付</button>
	</form>
	<p class="zh_tixing"><?php echo htmlspecialchars_decode($pageData['paytips']); ?></p>
</div>

</body>
</html>
