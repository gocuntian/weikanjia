<?php
/**
 * controller: 微砍价通用支付接口
 * create by sixian
 * @2015-05-04
 * ------------------
 */

//start session


/**----------------
 * include common files
 */

$pageDirPath = dirname(__FILE__);

include_once("{$pageDirPath}/WxPayPubHelper/WxPayPubHelper.php");
include_once("{$pageDirPath}/qrcode_maker.php");

/**----------------
 * controll logical code here
 * {{{
 */
session_start();
/**
 *接收参数
 *验证签名，
 *显示订单确认页
 *确认支付处理
 *保存支付流水数据
 *返回数据、状态
*/
$pageData['oid'] = "";

if( isset($_REQUEST['oid']) && isset($_REQUEST['apiurl']) && isset($_REQUEST['apimode']) 
	&& !empty($_REQUEST['oid']) && !empty($_REQUEST['apiurl']) && !empty($_REQUEST['apimode']) )
{	
	//接收过来的参数
	$_SESSION['oid'] = $_REQUEST['oid'];
	$_SESSION['repay'] = isset( $_REQUEST['repay'] ) ? $_REQUEST['repay'] : '0';
	$_SESSION['apiurl'] = $_REQUEST['apiurl'];
	$_SESSION['apimode'] = $_REQUEST['apimode'];

}else if( !empty($_SESSION['oid']) && !empty($_SESSION['apiurl']) ){

	//授权回来 读取$_SESSION参数 
	$pageData['oid'] = $_SESSION['oid'];
	$pageData['apiurl'] = $_SESSION['apiurl'];
	$api['oid'] = $pageData['oid'];
	$apidatas = IdeaUtil::curlGet($pageData['apiurl'], true, $api); //从api读取数据
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
	$pageData['orderid'] = $datas['orderid'];
	$pageData['updateapi'] = $datas['updateapi'];
	$_SESSION['oid'] =  $pageData['oid'] = $datas['orderid'];//原始订单号
	$pageData['pay_type'] = isset($datas['pay_type']) ? $datas['pay_type'] : '';// 0：微信支付与支付宝 1：微信支付 2：只支付宝
	$pageData['alipayurl'] = isset($datas['alipayurl']) ? $datas['alipayurl'] : '';//支付宝支付地址
	$pageData['paytips'] = isset($datas['paytips']) ? $datas['paytips'] : '(请在提交订单后两小时内付款，逾期将取消该订单!)';//支付宝支付地址

	$pageData['repay'] = $_SESSION['repay'];
	if( !empty($pageData['repay']) ){//重新支付参数 
		if(stristr($pageData['updateapi'],"?")){
			$pageData['updateapi'] = $pageData['updateapi'].'&';
		}else{
			$pageData['updateapi'] = $pageData['updateapi'].'?';
		}
		$pageData['updateapi'] = $pageData['updateapi'].'repay=1';
	}

    //添加随机数
    $pageData['orderid'] = $pageData['orderid'].'rp'.rand(1, 1000);//微信支付商户订单号



}
//从商户订单号中获取网站ID

$pageData['oid']= $_SESSION['oid'];
$sid = IdeaUtil::getSidFromOut_trade_no($_SESSION['oid']);

// 读取签名密钥 网址名
$privateKey = WxPayConf_pub::privateKey;
$sitename   = WxPayConf_pub::sitename;


/**
 * JS_API支付demo
 * ====================================================
 * 在微信浏览器里面打开H5网页中执行JS调起支付。接口输入输出数据格式为JSON。
 * 成功调起支付需要三个步骤：
 * 步骤1：网页授权获取用户openid
 * 步骤2：使用统一支付接口，获取prepay_id
 * 步骤3：使用jsapi调起支付
*/


	
	//使用jsapi接口
	$jsApi = new JsApi_pub();
	
	//=========步骤1：网页授权获取用户openid============
	//通过code获得openid
	if (!isset($_GET['code']))
	{
		// 触发微信返回code码
		$webname = "wxpayapi.php";
        $url     = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
        /*$newArr  = explode($webname, $url);
        $newUrl  = preg_replace('/code/', 'code_old', $newArr[1]);        
        $newUrl  = $newArr[0] . $webname . $newUrl;*/
        $newUrl = $url;
        //页面授权会返回地址
        $currentUrl = $newUrl;
        // 当前网址是否有参数判断
        $wxapiurl   = stristr($currentUrl,"?") ?  $currentUrl . '&showwxpaytitle=1' : $currentUrl . '?showwxpaytitle=1';
		$backUrl 	= $jsApi->createOauthUrlForCode($wxapiurl);
		header("Location: $backUrl"); exit;
		
	}else
	{
		//获取code码，以获取openid
	    $code = $_GET['code'];
		$jsApi->setCode($code);
		$openid = $jsApi->getOpenId();
	}
	//如果拿不到openid 将直接返回
	if( !isset($openid) || empty($openid) ){ //获取不到openid
		// PayMonit('wxpayfail','type=fail','1');//阿里监控器
		echo "获取不到openid";
		header("Location: {$pageData['backurl']}");
		exit();
	}



	
	//=========步骤2：使用统一支付接口，获取prepay_id============
	//使用统一支付接口
	$unifiedOrder = new UnifiedOrder_pub();
	
	//设置统一支付接口参数
	//设置必填参数
	//appid已填,商户无需重复填写
	//mch_id已填,商户无需重复填写
	//noncestr已填,商户无需重复填写
	//spbill_create_ip已填,商户无需重复填写
	//sign已填,商户无需重复填写

	//整理支付数据
	$p_title = $pageData['prouct_title'];//商品描述
	$p_fee = floor( $pageData['prouct_pay']*100 );//商品价格
	$out_trade_no = $pageData['orderid'];//商户订单号
	$attach = urldecode($pageData['updateapi']);//附加数据
	$sub_mch_id = $pageData['siteid'];//子商户订单号
	//$device = $pageData['device'];
	//$time_start = $pageData['time_start'];
	//$time_expire = $pageData['time_expire'];
	//$p_tag = $pageData['p_tag'];
	//$p_id = $pageData['prouct_id'];

	if( $p_fee==0 ){
        include "{$pageDirPath}/wxpayapizero.php";
        exit;
		/*$zeroUrl = 'wxpayapizero.php?oid='.$pageData['oid'].'&apimode=apipay&apiurl='.$_SESSION['apiurl'];
		header("Location: $zeroUrl");exit;*/
	}




    //测试用户金额变成1分钱  oLU6AjrX7dMgtqIMGW8yn2JM8yZ4 || oLU6Ajl8O6Hgou-drkISG2CiLHg8
    if (isset($openid) && ($openid == 'oLU6AjrX7dMgtqIMGW8yn2JM8yZ4' || $openid == 'oLU6Ajl8O6Hgou-drkISG2CiLHg8') ) {
        $p_fee = 1;
    }

	$unifiedOrder->setParameter("openid","$openid");//用户标识
	$unifiedOrder->setParameter("body","$p_title");//商品描述
	//自定义订单号，此处仅作举例
	
	$unifiedOrder->setParameter("out_trade_no","$out_trade_no");//商户订单号 
	$unifiedOrder->setParameter("total_fee",$p_fee);//总金额
	$unifiedOrder->setParameter("notify_url",WxPayConf_pub::NOTIFY_URL);//通知地址 
	$unifiedOrder->setParameter("trade_type","JSAPI");//交易类型
	//非必填参数，商户可根据实际情况选填
	//$unifiedOrder->setParameter("sub_mch_id","$sub_mch_id");//子商户号  
	//$unifiedOrder->setParameter("device_info","$device");//设备号 
	$unifiedOrder->setParameter("attach","$attach");//附加数据 
	//$unifiedOrder->setParameter("time_start","$time_start");//交易起始时间
	//$unifiedOrder->setParameter("time_expire","$time_expire");//交易结束时间 
	//$unifiedOrder->setParameter("goods_tag","$p_tag");//商品标记 
	//$unifiedOrder->setParameter("product_id","$p_id");//商品ID




    //支付交易5分钟后关闭
    $time_expire = date('YmdHis', time()+300);
	$unifiedOrder->setParameter("time_expire", "{$time_expire}");//交易结束时间


	$prepay_id = $unifiedOrder->getPrepayId();


    //}}}	
    if ( !isset($prepay_id) || empty($prepay_id) ) {//支付前流水号为空
    	echo "支付前流水号为空";
    	exit;
    }


	//=========步骤3：使用jsapi调起支付============
	$jsApi->setPrepayId($prepay_id);

	$jsApiParameters = $jsApi->getParameters();
	//echo $jsApiParameters;
	$pageData['jsApiParameters'] = $jsApiParameters;


    //保存数据
    $paydata['orderid'] = $out_trade_no;//微信支付商户订单
    $paydata['wxorderid'] = $prepay_id;
    $paydata['openid'] = $openid;
    $paydata['siteid'] = $pageData['siteid'];
    $paydata['payway'] = 'weixin';
    $paydata['payprice'] = $p_fee;
    $paydata['status'] = 0;//0:初始，1:取消，2:付款成功，3:失败，4:订单失效，
    $paydata['time'] = time();
    $paydata['product'] = $p_title;//商品标题
    $paydata['remark'] = 'create orders:'.date("Y/m/d H:i:s").';';

    // 云平台发送数据改为ajax异步{{{
    $postData['openid']  = $openid;
    $postData['sid']     = $sid;
    $postData['oid']     = $pageData['oid'];
    $postData['paydata'] = $paydata;
    $postData['action_old']['payid']  = $prepay_id;
    $suf = !empty($sid) ? ('_'.$sid) : '';//表后缀
	$postData['action_old']['suf']    = $suf;
	$postData['action_old']['type']   = 'wxpayapi';

    $ajaxUrl             = WxPayConf_pub::updateApiUrl;
    $url = $ajaxUrl.'?'.http_build_query($postData). '&v='. time();
    $apidatas = IdeaUtil::curlGet($url, true,array() );
    // }}}


    $pageData['uid'] = $openid;
	$pageData['wxorderid'] = $out_trade_no;//微信支付商户订单

	//增加参数 微信支付商户订单ID   微信openid
	if( empty($pageData['alipayurl']) ){ //如果没有传递 使用默认支付宝支付接口 //http://shop.xinyuemin.net/alipay/zfpayapi.php
		$pageData['alipayurl'] = 'http://shop.xinyuemin.net/test/rdsalipay/alipay/zfpayapi.php?apimode=apiurl&oid='.$pageData['oid'].'&apiurl='.$pageData['apiurl'].'&orderid='.$pageData['orderid'].'&openid='.$openid;
	}else{
		if(stristr($pageData['alipayurl'],"?")){
			$pageData['alipayurl'] = $pageData['alipayurl'].'&';
		}else{
			$pageData['alipayurl'] = $pageData['alipayurl'].'?';
		}
		$pageData['alipayurl'] = $pageData['alipayurl'].'apimode=apiurl&oid='.$pageData['oid'].'&apiurl='.$pageData['apiurl'].'&orderid='.$pageData['orderid'].'&openid='.$openid;  
	}


//生成支付二维码
$qrParas = array(
	'openid' => @$openid,
	'p_title' => @$p_title,
	'out_trade_no' => @$out_trade_no,
	'p_fee' => @$p_fee,
	'attach' => @$attach,
);
$qrcode4pay = getPayUrlForQRCode($qrParas);
$pageData['qrCodePayUrl'] = $qrcode4pay;	//二维码支付链接
/**----------------
 * }}}
 */



/**----------------
 * config title, description, keywords
*/
$pageTitle  = "支付确认-";
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
if($pageData['pay_type']==2){// 0：微信支付与支付宝 1：微信支付 2：只支付宝
	$viewName = 'alipay';         
}elseif($pageData['pay_type']==1){
	$viewName = 'wxpay';           
}else{
	$viewName = 'payapi';             
}

?>
<!DocType html>
<html>
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7">
	<meta charset="utf-8">

	<meta content="telephone=no" name="format-detection" />
	<link href="http://cdn.bootcss.com/bootstrap/3.3.4/css/bootstrap.min.css" rel="stylesheet">
	<link href="http://cdn.bootcss.com/bootstrap/3.3.4/css/bootstrap-theme.min.css" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="./theme/css/kanjia.css">


	<title><?php echo $pageTitle; ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
	<meta name="keywords" content="<?php echo $pageKeywords; ?>">
	<meta name="description" content="<?php echo $pageDescription; ?>">	
</head>
<body >

<div style="display:none;" id="paydata" data-backurl="<?php echo $pageData['backurl']; ?>" data-orderid="<?php echo $pageData['oid']; ?>" data-wxorderid="<?php echo $pageData['wxorderid']; ?>" data-uid="<?php echo $pageData['uid']; ?>"></div>
<div class="zh_pay">
	<div class="zh_xinxi clearfix">
		<div class="col-xs-3 quchu"><img src="<?php echo $pageData['prouct_pic']; ?>" /></div>
		<div class="col-xs-9 quchu zh_content">
			<h5><?php echo $pageData['prouct_title']; ?></h5>
			<p><?php echo $pageData['prouct_introduction']; ?></p>
		</div>
	</div>
	<div class="zh_jia">
		<p class="zh_yuan">原价：<span>￥<?php echo $pageData['prouct_price']; ?></span></p>
		<p class="zh_shi">实付款：<span>￥<?php echo $pageData['prouct_pay']; ?></span></p>
	</div>
	<div class="zh_fangshi">
		<p>选择支付方式</p>
		<div class="clearfix">
			<a href="javascript:;" class="col-xs-6 zh_weix">
				<img id="imgwxpay" class="active" src="theme/images/weixin.jpg" />
				<img id="zh_weix_select" class="zh_dui" style="display:block;" src="theme/images/dui.png" />
			</a>
		</div>
	</div>
	<input id="pay_type" name="pay_type" type="hidden"  value="wxpay">
	<a id="wx_zhifu" onclick="callpay()" class="zh_zhifu" data-paystatus="1">确认支付</a>
	<p class="zh_tixing"><?php echo htmlspecialchars_decode($pageData['paytips']); ?></p>
</div>


<!-- 二维码扫码支付 -->
<div id="qrCodePayUrl" style="text-align:center"></div>


<script src="http://cdn.bootcss.com/jquery/2.1.4/jquery.min.js"></script>
<script type="text/javascript">

	/* 调用微信JS api 支付 */
	function jsApiCall()
	{
		WeixinJSBridge.invoke(
			'getBrandWCPayRequest',
			<?php echo $pageData['jsApiParameters']; ?>,
			function(res){
				WeixinJSBridge.log(res.err_msg);
				var rest_msg = res.err_msg;
				//alert(res.err_code+res.err_desc+res.err_msg); 
				var orderid = $('#paydata').attr('data-orderid');
				var wxorderid = $('#paydata').attr('data-wxorderid');
				var uid = $('#paydata').attr('data-uid');
				var backUrl = $('#paydata').attr('data-backurl');
				var paystatus = rest_msg;

				if(backUrl){
					var backUrl = backUrl+'oid='+orderid+'&wxorderid='+wxorderid+'&openid='+uid+'&paystatus='+res.err_msg;
				}

				if(res.err_msg=='get_brand_wcpay_request:fail'){
					//alert('支付失败！');
				}else if(res.err_msg=='get_brand_wcpay_request:cancel'){
					//alert('支付取消！');
				}else if(res.err_msg=='get_brand_wcpay_request:ok'){
					/* 防止再次支付  */

					$('#zh_zhifu').css('display', 'none');
					$('#zh_zhifu').after('<a href="'+backUrl+'" class="zh_zhifu" data-paystatus="2">已成功支付</a>');
				}
				
				//根据backurl重定向回去 订单号，微信payid ，openid
				if(backUrl){
					window.setTimeout(function () {
						window.location.href = backUrl;
					}, 2*1000);
				}

				/*if(res.err_msg=='get_brand_wcpay_request:cancel'){
					alert('付款取消');
				}else if(res.err_msg=='get_brand_wcpay_request:ok'){
					alert('付款成功！');
				}*/
				/*alert(res.err_code+res.err_desc+res.err_msg); */
			}
		);
	}

	function callpay()
	{
		if (typeof WeixinJSBridge == "undefined"){
		    if( document.addEventListener ){
		        document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);
		    }else if (document.attachEvent){
		        document.attachEvent('WeixinJSBridgeReady', jsApiCall); 
		        document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
		    }
		}else{
		    jsApiCall();
		}
	}
/********** 支付方式选择 **************/
document.addEventListener('WeixinJSBridgeReady', function() {
     WeixinJSBridge.call('hideOptionMenu');
});
</script>


<script src="theme/js/wxqrcode.js"></script>
<script>
	if(<?php echo isset($pageData['qrCodePayUrl']) && !empty($pageData['qrCodePayUrl']) ? 1 : 0; ?>)
	{
		var url = "<?php echo $pageData['qrCodePayUrl']; ?>";
		//参数1表示图像大小，取值范围1-10；参数2表示质量，取值范围'L','M','Q','H'
		var qr = qrcode(7, 'M');
		qr.addData(url);
		qr.make();
		var wording=document.createElement('p');
		wording.innerHTML = "遇到跨号支付问题，请长按二维码支付";
		var code=document.createElement('DIV');
		code.innerHTML = qr.createImgTag();
		var element=document.getElementById("qrCodePayUrl");
		element.appendChild(code);
		element.appendChild(wording);
	}
</script>
</body>
</html>
