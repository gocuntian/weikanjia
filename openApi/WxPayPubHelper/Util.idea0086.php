<?php 
/**
 * ----------------------------------------------
 * idea 订单变更 接口类
 * 该类实现了 验证签名后 变更订单订单，流程如下：
 * 1、根据后台配置的 idea签名密钥  得到签名密钥
 * 2、签名验证通过后 向idea云平台 发送支付流水信息保存到云平台
 * ----------------------------------------------
 *
 * 该类是 是私有类 可迭加 私有方法 及 常用类
 * createTime 2015-05-05
 * @author sixian
 *
 */
class IdeaUtil
{


    /**
	 * @param  out_trade_no   微砍价订单号
	 * @return sid            得到idea 云平台网站id
     */
    public static function getSidFromOut_trade_no($out_trade_no) {
        $thisYear = date('Y');
        if (strpos($out_trade_no, $thisYear) !== false) {
            $arr = explode($thisYear, $out_trade_no);
            return isset($arr[0]) ? $arr[0] : 0;
        }

        return 0;
    }
    /**
	 * @param  url        请求的链接
	 * @param  post       是否为post模式
	 * @param  postFields post的数据
	 * @param  timeout    超时时间
	 * @return data       返回的数据
     */
    public static function curlGet($url, $post = false, $postFields = array(), $timeout = 2) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

        if ($post && !empty($postFields)) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
        }

        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    /**
	 * @param  timestamp 时间戳
	 * @param  nonce 唯一值 此处为openid 
	 * @param  privateKey 密钥
	 * @return signature 得出的密钥
     */
    public static function generateSignature($timestamp, $nonce, $privateKey = '') {
        $token  = $privateKey;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $signature = sha1( $tmpStr );
 
        return $signature;
    }

    /**
	 * @param  signature 传入的签名
	 * @param  timestamp 时间戳
	 * @param  nonce 唯一值 此处为openid 
	 * @param  privateKey 密钥
	 * @return false Or true 验证是否成功
     */
    public static function checkSignature($signature,$timestamp,$nonce,$privateKey = '') {
        if( empty($signature) ){return false;}
        $time = time();
        if ($time - $timestamp > 10) {
            return false;
        }
 
        $token = empty($privateKey) ? YUNAPPSECRET : $privateKey;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
 
        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }


    /**
     * @param  url 当前的网站
     * @return 返回域名
     */   
    public static function getHostFromReferer($url = '') {
        if (empty($url)) {
            $url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
        }
        if (empty($url)) {return '';}

        $arr = parse_url($url);
        return isset($arr['host']) ? $arr['host'] : '';
    }


    /**
     * @param  webname 需要进入的控制器
     * @return 获取运行文件所在的网址路径 缺省运行文件时使用
     */   
    public static function getweburl( $webname = 'index.php' ){
        $url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
        $arr = parse_url($url);
        return 'http://'.$arr['host'] . str_replace($webname, '', $arr['path']);
    }

    /**
     * @param  webname 需要进入的控制器
     * @return 获取运行文件所在的网址路径
     */   
    public static function getwebpath($webname = 'index.php'){
        $url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
        $url = explode($webname, $url);
        return $url[0];
    }



}


?>