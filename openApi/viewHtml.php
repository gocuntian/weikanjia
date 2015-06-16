<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="format-detection" content="telephone=no" />
<meta name="apple-mobile-web-app-status-bar-style" content="black" />
<meta name="description" content=""/>
<meta name="keywords" content=""/>
<meta name="shareTitle" content="">
<meta name="shareLogo" content="http://xinyuemin.com/invitationHq/fenxiang3.png">
<title>付款页面</title>
<link rel="stylesheet" type="text/css" href="./theme/css/admin.css?<?php echo time(); ?>">
</head>

<body>

<div class="payment">
	<div class="addresSelect" id="addresSelect">
		<ul class="ul1 gyDizhiK">
			<!-- 自动追加的商品 -->
		</ul>
	</div>
	<div id="addressK" class="addressK">
		<div class="leftK">收货地址</div>
		<p class="shuxian"></p>
		<div class="rightK gyDizhiK" id="addressMain">
			<p><span><?php echo $pageData['realname'];?></span><a href="javascript:;"><?php echo $pageData['cellphone'];?></a></p>
			<p><?php echo $pageData['address'];?></p>
		</div>
	</div>
	<div class="payDetail">
		<div class="goodsInfo clearfix">
			<div class="leftImg"><img class="img-responsive" src="<?php echo $pageData['prouct_pic']; ?>" alt="图片"/></div>
			<div class="rightText">
				<strong><?php echo $pageData['prouct_title']; ?></strong>
				<p><?php echo htmlspecialchars_decode($pageData['prouct_introduction']); ?></p>
			</div>
		</div>
		
		<?php if(($pageData['coupon_money']) != '0.00'){?>
		<div class="preferentialD">
			<strong>优惠劵<span>（1张优惠劵）</span></strong>
			<p class="juanMain"><img src="./theme/images/shangpintp2.jpg" alt="图片"/><span><em><?php echo $pageData['coupon_money']; ?></em>元优惠e劵</span></p>
		</div>
		<?php }?>

		<div class="truePayment">
			<p class="shichang">市场价：<span>￥<?php echo $pageData['prouct_price']; ?></span></p>
            <?php if(($pageData['coupon_money']) != '0.00'){?>
			<p>优惠劵：-￥<?php echo $pageData['coupon_money']; ?></p>
            <?php }?>
			<p class="shifukuan">实付款：<span>￥<?php echo $pageData['prouct_pay']; ?></span></p>
		</div>
		<div class="payConfirm">
			<a class="anniuK gyStyleZfYi" href="javascript:;"  id="wx_zhifu" onclick="callpay()" class="zh_zhifu" data-paystatus="1" ><img src="./theme/images/shangpintp3.jpg" alt="图片"/><span>微信支付</span></a>
			<p class="shuomingK gyStyleZfYi"><img src="./theme/images/shangpintp4.jpg" alt="图片"/><span>遇到支付问题，请长按二维码支付</span></p>
			<!-- <img id="qrCodePayUrl" class="erweimaK" src="" alt="图片"/> -->
			<div id="qrCodePayUrl"  style="text-align:center"></div>
		</div>		
	</div>
	<div id="zhegaik" class="zhegaik"></div>	
</div>

<script type="text/javascript" src="./theme/js/jquery-1.11.0.min.js"></script>
<script type="text/javascript" src="./theme/js/admin.js?v=<?php echo time();?>"></script>
<script src="theme/js/wxqrcode.js"></script>

<div style="display:none;" id="paydata" 
data-backurl="<?php echo $pageData['backurl']; ?>"
data-orderid="<?php echo $pageData['oid']; ?>"
data-wxorderid="<?php echo $pageData['wxorderid']; ?>" 
data-uid="<?php echo $pageData['uid']; ?>"
data-addressAjax="http://m2.wkj.idea0086.com/?controller=user&action=postAddress&xymopenid=<?php echo $pageData['xymopenid']; ?>"
>
 </div>
<script type="text/template" id='addressList'>
<li data-realname="{ra_realname}" data-cellphone="{ra_cellphone}"  data-address="{ra_address}" >
	<p><span >{ra_realname}</span><a href="javascript:;" >{ra_cellphone}</a></p>
	<p>{ra_address}</p>
</li>	
</script>

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

					if(backUrl){
						window.setTimeout(function () {
							window.location.href = backUrl;
						}, 500);
					}						
					//alert('支付失败！');
				}else if(res.err_msg=='get_brand_wcpay_request:cancel'){
					//alert('支付取消！');
				}else if(res.err_msg=='get_brand_wcpay_request:ok'){
					/* 防止再次支付  */
					$('#zh_zhifu').css('display', 'none');
					$('#zh_zhifu').after('<a href="'+backUrl+'" class="zh_zhifu" data-paystatus="2">已成功支付</a>');
					//根据backurl重定向回去 订单号，微信payid ，openid
					if(backUrl){
						window.setTimeout(function () {
							window.location.href = backUrl;
						}, 500);
					}					
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

if(<?php echo isset($pageData['qrCodePayUrl']) && !empty($pageData['qrCodePayUrl']) ? 1 : 0; ?>)
{
	var url = "<?php echo $pageData['qrCodePayUrl']; ?>";
	//参数1表示图像大小，取值范围1-10；参数2表示质量，取值范围'L','M','Q','H'
	var qr = qrcode(10, 'M');
	qr.addData(url);
	qr.make();
	var wording=document.createElement('p');
	wording.innerHTML = "";
	var code= document.createElement('DIV');
	code.innerHTML = qr.createImgTag();
	var element=document.getElementById("qrCodePayUrl");
	element.appendChild(code);
	element.appendChild(wording);
}
</script>

</body>
</html>
