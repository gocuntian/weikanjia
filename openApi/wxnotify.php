<?php
/**
 * controller: 回调通知接口
 * create by sixian
 * @2015-05-04
 * ------------------
 */

/**
 * 
 * ====================================================
 * 支付完成后，微信会把相关支付和用户信息发送到商户设定的通知URL，
 * 商户接收回调信息后，根据需要设定相应的处理流程。
 * 
 * 这里举例使用log文件形式记录回调信息。
*/
	$incPaths = dirname(__FILE__);
	include_once("{$incPaths}/log_.php");
	include_once("{$incPaths}/WxPayPubHelper/WxPayPubHelper.php");

    //使用通用通知接口
	$notify = new Notify_pub();

	//存储微信的回调
	$xml = $GLOBALS['HTTP_RAW_POST_DATA'];
	$notify->saveData($xml);

	//验证签名，并回应微信。
	//对后台通知交互时，如果微信收到商户的应答不是成功或超时，微信认为通知失败，
	//微信会通过一定的策略（如30分钟共8次）定期重新发起通知，
	//尽可能提高通知的成功率，但微信不保证通知最终能成功。

	if($notify->checkSign() == FALSE){
		$notify->setReturnParameter("return_code","FAIL");//返回状态码
		$notify->setReturnParameter("return_msg","签名失败");//返回信息
	}else{
		$notify->setReturnParameter("return_code","SUCCESS");//设置返回码
	}
	$returnXml = $notify->returnXml();
	echo $returnXml;
	
	//==商户根据实际情况设置相应的处理流程，此处仅作举例=======
	
	//以log文件形式记录回调信息
	$log_ = new Log_();
	$log_name="./log/notify_url.log";//log文件路径
	$log_->log_result($log_name,"【接收到的notify通知】:\n".$xml."\n");

	if($notify->checkSign() == TRUE)
	{
		if ($notify->data["return_code"] == "FAIL") {
			//此处应该更新一下订单状态，商户自行增删操作
			$log_->log_result($log_name,"【通信出错】:\n".$xml."\n");
			$paystatus = 'get_brand_wcpay_request:cancel';
			$notify_status = '支付失败，通信出错';

			//PayMonit('alipayfail','type=fail','1');//阿里监控器
		}
		elseif($notify->data["result_code"] == "FAIL"){
			//此处应该更新一下订单状态，商户自行增删操作
			$log_->log_result($log_name,"【业务出错】:\n".$xml."\n");
			$paystatus = 'get_brand_wcpay_request:cancel';
			$notify_status = '支付失败，业务出错';

			//PayMonit('alipayfail','type=fail','1');//阿里监控器
		}
		else{
			//此处应该更新一下订单状态，商户自行增删操作
			$log_->log_result($log_name,"【支付成功】:\n".$xml."\n");
			$paystatus = 'get_brand_wcpay_request:ok';
			$notify_status = '支付成功！';

			//PayMonit('wxpaysuccess','type=success','1');//阿里监控器
		}
		
/**
 * 商户自行增加处理流程,
 * 更新订单状态 数据库操作
 * 推送支付完成信息
 * ------------------
 * 获取网站id 网站动态密钥
 * 获取参数
 * 请求API更新订单状态
 * 更新订单流水状态
 */
	
        //获取通知数据
		$notifydata = $notify->getData();
		//通知数据转成字符方便技术监控
		$notify_data = '';
		foreach ($notifydata as $ntkey => $ntdata) {
			$notify_data .= $ntkey.':'.$ntdata.';\n';
		}
		@error_log("wuyi wxnotify1: {$notify_data} \n", 3, './log/debug.log');
		//变量赋值
		$up_url = isset($notifydata['attach']) ? urldecode($notifydata['attach']) : '';//数据更新地址
		$up_openid = isset($notifydata['openid']) ? $notifydata['openid'] : '';//支付者openid
		$up_out_trade_no = isset($notifydata['out_trade_no']) ? $notifydata['out_trade_no'] : '';//商户订单号
		$up_total_fee = isset($notifydata['total_fee']) ? $notifydata['total_fee'] : '';//微信支付金额
		$up_transaction_id = isset($notifydata['transaction_id']) ? $notifydata['transaction_id'] : '';//微信支付订单号
		$up_return_code = isset($notifydata['return_code']) ? $notifydata['return_code'] : '';//微信支付返回状态
		$up_result_code = isset($notifydata['result_code']) ? $notifydata['result_code'] : '';//微信支付业务状态


		//从商户订单号中获取网站ID，从而获取该网站的动态密钥  
        $sid = IdeaUtil::getSidFromOut_trade_no($up_out_trade_no);

        $privateKey = WxPayConf_pub::privateKey;
        $sitename   = WxPayConf_pub::sitename;



		if( $paystatus == 'get_brand_wcpay_request:ok' && !empty($up_url) ){//支付成功

			//加入订单号包涵 "rp"分割字符串得到原始商户订单号用于api订单更新
			if ( strpos($up_out_trade_no,'rp') ) {
				$arr_out_trade_no = explode('rp', $up_out_trade_no);
				$up_out_trade_no = $arr_out_trade_no[0];
			}

			//生成签名
			$privateKey = isset($privateKey) ? $privateKey : '515xinyuemin'; //获取不到密钥，就用默认密钥
        	$timestamp = time();
        	$nonce = md5( base64_encode($up_openid.$up_out_trade_no.$up_total_fee.'xym') );
        	$signature = IdeaUtil::generateSignature($timestamp , $nonce  , $privateKey);
        	//为添加数据做准备
        	if(stristr($up_url,"?")){
				$up_url = $up_url.'&';
			}else{
				$up_url = $up_url.'?';
			}
			//请求api更新订单状态
			$up_url = $up_url.'signature='.$signature.'&timestamp='.$timestamp.'&nonce='.$nonce;
			$updata['oid'] = $up_out_trade_no;
			//$updata['status'] = 2;
			$updata['wxpayid'] = $up_transaction_id;
			$updata['openid'] = $up_openid;
			$updata['paytype'] = 'wxpay';
			$up_rest = IdeaUtil::curlGet($up_url, true, $updata);
			$rst = json_decode($up_rest,true);
			@error_log("wuyi wxnotifyapi1: ".json_encode($updata)." \n privateKey:$privateKey ; sid:$sid; $up_url \n", 3, './log/debug.log');

			//改变订单流水状态
			$uppaydata['status'] = 2; //0:初始，1:取消，2:付款成功，3:失败，4:订单失效，
			$uppaydata['payway'] = 'wxpay';//weixin：微信预支付，wxpay：微信支付，alipay：支付宝支付
			$uppaydata['time'] = time();
			$uppaydata['wxorderid'] = $up_transaction_id;//微信订单号
			$payid = $notifydata['out_trade_no'];//商户微信支付订单号
			$suf = !empty($sid) ? ('_'.$sid) : '';//表后缀

			// 将数据传送到 支付平台
		    // 云平台发送数据改为ajax异步{{{
		    $postData['openid']  = $updata['openid'];
		    $postData['sid']     = $sid;
		    $postData['oid']     = $updata['oid'];
		    $postData['paydata'] = $uppaydata;
		    $postData['action_old']['payid']  = $payid;
			$postData['action_old']['suf']    = $suf;
		    $postData['action_old']['type']   = 'updatePayrecordByOrderidHasTabsuf';

		    $ajaxUrl             = WxPayConf_pub::updateApiUrl;
		    $url = $ajaxUrl.'?'.http_build_query($postData). '&v='. time();
		    $apidatas = IdeaUtil::curlGet($url, true,array() );
		    // }}}
		}

		if( $paystatus == 'get_brand_wcpay_request:cancel' && !empty($up_url) ){ //支付失败
			//改变订单状态
			$uppaydata['status'] = 3; //0:初始，1:取消，2:付款成功，3:失败，4:订单失效，
			$uppaydata['payway'] = 'wxpay';//weixin：微信预支付，wxpay：微信支付，alipay：支付宝支付
			$uppaydata['time'] = time();
			$uppaydata['wxorderid'] = $up_transaction_id;//微信订单号
			$payid = $notifydata['out_trade_no'];//商户微信支付订单号
			$suf = !empty($sid) ? ('_'.$sid) : '';//表后缀

		    // 云平台发送数据改为ajax异步{{{
		    $postData['openid']  = $updata['openid'];
		    $postData['sid']     = $sid;
		    $postData['oid']     = $updata['oid'];
		    $postData['paydata'] = $uppaydata;
		    $postData['action_old']['payid']  = $payid;
			$postData['action_old']['suf']    = $suf;
		    $postData['action_old']['type']   = 'updatePayrecordByOrderidHasTabsuf';
		    $ajaxUrl             = WxPayConf_pub::updateApiUrl;
		    $url = $ajaxUrl.'?'.http_build_query($postData). '&v='. time();
		    $apidatas = IdeaUtil::curlGet($url, true,array() );
		    // }}}
		}


	
		
	}

exit;
/**----------------
 * }}}
 */

