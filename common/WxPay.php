<?php

namespace common;

use Yii;

class WxPay
{

    private $appid = '';
    private $partnerId = '';
    private $key = '';
    private $notify_url = '';
    const URL = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
    const REFUND_URL = 'https://api.mch.weixin.qq.com/secapi/pay/refund';

    public function __construct($pay_version = '')
    {
        if ('prod' == YII_ENV) {
            if ($pay_version == 'ios_v2') {  //正式犀京iOS微信支付
                $this->appid = 'wx20d760e352ffe382';
                $this->partnerId = '1600071962';
                $this->notify_url = 'http://api.xykj1.com/goods-order/yibu?pay_version=ios_v2';
                $this->key = 'XIJINxijing2020weixinapi0615abcd';
            } else {
                $this->appid = 'wx20d760e352ffe382';
                $this->partnerId = '1600071962';
                $this->notify_url = 'http://api.xykj1.com/goods-order/yibu';
                $this->key = 'XIJINxijing2020weixinapi0615abcd';
            }
        } else {
            if($pay_version == 'xijing_v3'){  // 无语
                $this->appid = 'wx20d760e352ffe382';
                $this->partnerId = '1600071962';
                $this->notify_url = 'http://47.103.61.179:1080/goods-order/yibu?pay_version=xijing_v3';
                $this->key = 'XIJINxijing2020weixinapi0615abcd';
            }
            if($pay_version == 'ios_v2'){
                $this->appid = 'wx20d760e352ffe382';
                $this->partnerId = '1600071962';
                $this->notify_url = 'http://47.103.61.179:1080/goods-order/yibu?pay_version=ios_v2';
                $this->key = 'XIJINxijing2020weixinapi0615abcd';
            }else{
                $this->appid = 'wx20d760e352ffe382';
                $this->partnerId = '1600071962';
                $this->notify_url = 'http://47.103.61.179:1080/goods-order/yibu';
                $this->key = 'XIJINxijing2020weixinapi0615abcd';
            }
        }

    }

    //生成订单
    public function wechat_pay($body, $out_trade_no, $price, $type)
    {
        $data["appid"] = (string)$this->appid;
        $data["mch_id"] = (string)$this->partnerId;
        $data["nonce_str"] = (string)$this->getRandChar(32);
        $data["body"] = (string)$body;//商品描述
        $data["notify_url"] = (string)$this->notify_url;
        $data["out_trade_no"] = (string)$out_trade_no;//订单号
        $data["spbill_create_ip"] = (string)$this->get_client_ip();
        $data["total_fee"] = (int)$price;//金额单位为分
        $data["trade_type"] = $type;//支付类型
        $time_expire = date('YmdHis', time() + 7200);  //失效时间2小时
        $data["time_expire"] = (string)$time_expire;
        $sign = $this->getSign($data);
        $data["sign"] = $sign;
        //配置xml最终得到最终发送的数据
        $formData = $this->data_to_xml($data);
        //以XML格式请求微信支付订单创建接口
        $response = $this->postXmlCurl($formData, self::URL);
        //将微信返回的结果xml转成数组
        $params = (array)simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOCDATA);

        if ($params['return_code'] != 'SUCCESS') {
            return $params;
        } else {
            $timestamp = time();
            //接收微信返回的数据,传给APP!
            $arr = array(
                'prepayid' => $params['prepay_id'],
                'appid' => $this->appid,
                'partnerid' => $this->partnerId,
                'package' => 'Sign=WXPay',
                'noncestr' => $data["nonce_str"],
                'timestamp' => strval($timestamp),
            );
            //必须二次签名再传给APP
            $s = $this->getSign($arr);
            $arr['sign'] = $s;
            $arr = json_encode($arr);
            $time = date("Y-m-d H:i:s");
            file_put_contents(Yii::$app->getRuntimePath() . "/logs/wx_order.log", $time . ',' . $out_trade_no . ',' . $price . ',过期时间:' . $time_expire . ',' . implode(',', $params) . PHP_EOL, FILE_APPEND);
            return $arr;
        }
    }

    //微信退款
    public function refund($order_sign, $price)
    {
        $data["appid"] = (string)$this->appid;
        $data["mch_id"] = (string)$this->partnerId;
        $data["nonce_str"] = (string)$this->getRandChar(32);
        $data["out_refund_no"] = (string)$order_sign;
        $data["out_trade_no"] = (string)$order_sign;
        $data["refund_fee"] = (string)$price;
        $data["total_fee"] = (string)$price;
        $sign = $this->getSign($data);
        $data["sign"] = $sign;
        //携带sign的数组转为XML
        $formData = $this->data_to_xml($data);
        //以XML格式请求微信退款接口
        $response = $this->postXmlCurl($formData, self::REFUND_URL);
        $time = date("Y-m-d H:i:s");
        //将微信返回XML转为数组
        $params = (array)simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOCDATA);

        if ($params['return_code'] != 'SUCCESS') {
            //return_code为FAIL请求错误
            file_put_contents(Yii::$app->getRuntimePath() . "/logs/wx_refund.log", $time . ',' . $order_sign . ',' . $price . ',' . implode(',', $params) . PHP_EOL, FILE_APPEND);
            return false;
        } else {
            //result_code为FAIL时查看返回错误码
            if ($params['result_code'] != 'SUCCESS') {
                file_put_contents(Yii::$app->getRuntimePath() . "/logs/wx_refund.log", $time . ',' . $order_sign . ',' . $price . ',' . implode(',', $params) . PHP_EOL, FILE_APPEND);
                return false;
            } else {
                file_put_contents(Yii::$app->getRuntimePath() . "/logs/wx_refund.log", $time . ',' . $order_sign . ',' . $price . ',' . implode(',', $params) . PHP_EOL, FILE_APPEND);
                return true;
            }
        }
    }

    //进行签名
    function getSign($Obj)
    {
        foreach ($Obj as $k => $v) {
            $arr[strtolower($k)] = $v;
        }
        ksort($arr);
        $string = $this->ToUrlParams($arr);
        $string = $string . "&key=" . $this->key;
        $string = md5($string);
        $paySign = strtoupper($string);
        return $paySign;
    }

    public function https_request($url, $post_data = '', $timeout = 5)
    {//curl
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        if ($post_data != '') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $file_contents = curl_exec($ch);
        curl_close($ch);
        return $file_contents;
    }

    public function data_to_xml($params)
    {
        if (!is_array($params) || count($params) <= 0) {
            return false;
        }
        $xml = "<xml>";
        foreach ($params as $key => $val) {
            // if (is_numeric($val)){
            $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            // }else{
            //     $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            // }
        }
        $xml .= "</xml>";
        return $xml;
    }

    //获取指定长度的随机字符串
    private function getRandChar($length)
    {
        $str = null;
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol) - 1;

        for ($i = 0; $i < $length; $i++) {
            $str .= $strPol[rand(0, $max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
        }

        return $str;
    }

    public function ToUrlParams($arr)
    {
        $buff = "";
        foreach ($arr as $k => $v) {
            if ($k != "sign" && $v != "" && !is_array($v)) {
                $buff .= $k . "=" . $v . "&";
            }
        }
        $buff = trim($buff, "&");
        return $buff;
    }

    //获取当前服务器的IP
    function get_client_ip()
    {
        if ($_SERVER['REMOTE_ADDR']) {
            $cip = $_SERVER['REMOTE_ADDR'];
        } elseif (getenv("REMOTE_ADDR")) {
            $cip = getenv("REMOTE_ADDR");
        } elseif (getenv("HTTP_CLIENT_IP")) {
            $cip = getenv("HTTP_CLIENT_IP");
        } else {
            $cip = "unknown";
        }
        return $cip;
    }

    //将数组转成uri字符串
    function formatBizQueryParaMap($paraMap, $urlencode)
    {
        $buff = "";
        $reqPar = '';
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if ($urlencode) {
                $v = urlencode($v);
            }
            $buff .= strtolower($k) . "=" . $v . "&";
        }
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }

    //数组转xml
    function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";

            } else
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
        }
        $xml .= "</xml>";
        return $xml;
    }

    //post https请求，CURLOPT_POSTFIELDS xml格式
    function postXmlCurl($xml, $url, $second = 30)
    {
        //初始化curl
        $ch = curl_init();
        //超时时间
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        //这里设置代理，如果有的话
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //默认格式为PEM，可以注释
        curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
        curl_setopt($ch, CURLOPT_SSLCERT, getcwd() . '/apiclient_cert.pem');
        //默认格式为PEM，可以注释
        curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
        curl_setopt($ch, CURLOPT_SSLKEY, getcwd() . '/apiclient_key.pem');

        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            return false;
        }
    }

    //xml转成数组
    public function xmlstr_to_array($xmlstr)
    {
        $doc = new \DOMDocument();
        $doc->loadXML($xmlstr);
        return $this->domnode_to_array($doc->documentElement);
    }

    public function domnode_to_array($node)
    {
        $output = array();
        switch ($node->nodeType) {
            case XML_CDATA_SECTION_NODE:
            case XML_TEXT_NODE:
                $output = trim($node->textContent);
                break;
            case XML_ELEMENT_NODE:
                for ($i = 0, $m = $node->childNodes->length; $i < $m; $i++) {
                    $child = $node->childNodes->item($i);
                    $v = $this->domnode_to_array($child);
                    if (isset($child->tagName)) {
                        $t = $child->tagName;
                        if (!isset($output[$t])) {
                            $output[$t] = array();
                        }
                        $output[$t][] = $v;
                    } elseif ($v) {
                        $output = (string)$v;
                    }
                }
                if (is_array($output)) {
                    if ($node->attributes->length) {
                        $a = array();
                        foreach ($node->attributes as $attrName => $attrNode) {
                            $a[$attrName] = (string)$attrNode->value;
                        }
                        $output['@attributes'] = $a;
                    }
                    foreach ($output as $t => $v) {
                        if (is_array($v) && count($v) == 1 && $t != '@attributes') {
                            $output[$t] = $v[0];
                        }
                    }
                }
                break;
        }
        return $output;
    }

    //微信支付成功以后的回调再次确认订单状态
    public function notify($out_trade_no)
    {
            /*
            //效验签名
            $sign = $this->getSign($result);
            if ($sign == $result['sign']) {
                return $result;
            } else {
                return false;
            }
            */
            //调用微信确认订单状态
            $res = $this->queryOrder($out_trade_no);
            return $res;
    }


    //订单状态查询
    public function queryOrder($out_trade_no)
    {
        $url = "https://api.mch.weixin.qq.com/pay/orderquery";
        $data = array(
            'appid' => (string)$this->appid,
            'mch_id' => (string)$this->partnerId,
            'out_trade_no' => (string)$out_trade_no,
            'nonce_str' => (string)$this->getRandChar(32)
        );
        $sign = $this->getSign($data);
        $data["sign"] = $sign;
        /*
        $xml_data = '<xml>
                   <appid>%s</appid>
                   <mch_id>%s</mch_id>
                   <nonce_str>%s</nonce_str>
                   <out_trade_no>%s</out_trade_no>
                   <sign>%s</sign>
                </xml>';
        */
        $formData = $this->data_to_xml($data);
        $response = $this->postXmlCurl($formData, $url);
        //将微信返回的结果xml转成数组
        $params = (array)simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($params['return_code'] == 'SUCCESS' && $params['result_code'] == 'SUCCESS') {
            return $params;
            /*
            if ($params['trade_state'] == 'SUCCESS') {
                return $params;
            } else {

                return array(
                    '交易类型' => $params['trade_type'],
                    '支付状态' => $params['trade_state'],
                    '总金额' => $params['total_fee'] / 100,
                    '订单号' => $params['out_trade_no'],
                    '支付完成时间' => $params['time_end']
                );
            }
            */
        } else {
            return false;
        }
    }


}