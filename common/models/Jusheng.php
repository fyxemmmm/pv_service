<?php

namespace common\models;
use common\Helper;


/*
 * 巨省会员对接
 * */
class Jusheng
{
    public $modelClass = '';
    private static $aesKey;  // 对称加密aesKey
    private static $channelCode;
    private static $jushengPubKey;
    private static $jushengMd5Key;
    private static $myPrivateKey;
    private static $jushengHost;

    public function __construct()
    {
        // aes加密秘钥 我方的 不变
        self::$aesKey = 'xiyiKeji12345678';
        // 我方私钥 测试正式都这个 用于解密aes
        self::$myPrivateKey = 'MIICdwIBADANBgkqhkiG9w0BAQEFAASCAmEwggJdAgEAAoGBAIgZRU1yPM4sk59g29swlr41vADAYyFTVVzT2KqzLZVvptQQ5qTnb7GyRjZa/iNu4/JYgNS9+I7rPFANnMNC9xuFcLQXIOexWQ2pG1p8kAhTseA5Ntk2u5hKUPDlGDiua1OOtOzdIB8k0aZn+NOqeHHKA8DzQllQyq1Ooxzuhc0dAgMBAAECgYAo53d1xFzUFn+zyhep9BuKRXogu7HPhU1FngIjo8CShkEcwYugilJ+lLcXhReWrLBj2Qe3aPU4dyjkYoL4CLtdnHmtkE2SbhRtv9+De8YZCXuVe26SWyPpH+pm2tz/gB9nWAwthnNfIykPf7clpRm5CdlwB+y3xkiqkKnzkt/SAQJBAPdlQAkoYq2snJz6SzT3xOlflhsWBE7/JqATZ0r4GNaCNuI4n64p570ESwDoqmcTKnV14ikzHODk0rr7hJKzj4ECQQCM1Q+QEWmBInFUUtH4M79smc/bCZvkPIm8zl1vZMGdQb6snyoss6AqpjBVcaVyCWbXILyg9B2Ocqa2/+FJXEudAkEA9sTVAg15mJkTwvVa7SO0E8EXvj7Q32dcxNF19+7O/7D1Q+ONS7FbCUxt8CMkThptAKssg0ILrTwyAvb9f0OCgQJAZTaKpMOey4vxXuTWukaWgHpag9T4EKPhaav2QqRq8ZXabz1EljPSHFSrz/yJJtfS7TE9338R7to/Lq3fP1XxkQJBALt444vguKinoVi3c9L07RXHmhr6VBChiKZdC+p6AB1pUEzcnMlzkB6wcIGTdECEa9HIKF00z/lm4399DvZjylw=';

        self::$channelCode = "xj"; // 渠道码
        if ('prod' == YII_ENV) {
            self::$jushengPubKey = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCmPdq4v1ra++vQVX+PonSTw+t0YGtpv9yhtltFE5u1gm1UuyeKgBZngODpmGN/HHWI231zJuBy+VGCjmDClV86EUmobj8agDQqn+5TmW+mlYECWOPSUE6vjD9GepPF+NwfD9xl2wVIJG95YbwrOzCrHK8So792LGLdcWTraMkn3QIDAQAB';
            self::$jushengMd5Key = '3977b0bd83ff6de6c25bffad1d640333';
            self::$jushengHost = 'https://card-app.pmstar.cn';
        }else{
            // 巨省公钥
            self::$jushengPubKey = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCxwVgrc8jxz3Nd2pjVDFw2uPVsK9z4D9PraX8oXDwqVaysgJg9cjOJlx9hg2sraP4ogJl4p8/pr2fX+C+Hj0aSjb4E3UOeR6JL6XnJFyCJTCIdfaG8/TH7/+6IAFbnVVqn9o6cCXBxDs0vJLKw37KaFZcVOVBjaZxyNtwGZUgTFQIDAQAB';
            self::$jushengMd5Key = 'WZY22BMB9MIP8Z95SSB0Z3RXVMUJUKMR';
            self::$jushengHost = 'https://ajqh-app-uat.apass.cn';
        }
    }

    /*
     * 验签
     * */
    protected function checkSign($post){
        try{
            $sign = $post['sign'];
            unset($post['sign']);
            $data = $this->getSignContent($post);
            $md5 = md5($data.self::$jushengMd5Key);
            $md5 = strtoupper($md5);
            return $md5 === $sign;
        }catch (\Exception $e){
            return false;
        }catch (\Throwable $e){
            return false;
        }
    }

    /*
     * 解析请求数据 业务参数
     * */
    public function parseData($post){
        $bool = $this->checkSign($post);
        if(!$bool) $this->response("0001","验证签名失败!");

        $secret_key = $post['secret_key'];
        $res = "-----BEGIN RSA PRIVATE KEY-----\n" .
            wordwrap(self::$myPrivateKey, 64, "\n", true) .
            "\n-----END RSA PRIVATE KEY-----";
        openssl_private_decrypt(base64_decode($secret_key),$aesKey,$res);

        $biz_data = $post['biz_data']; // 业务数据
        $biz_data =  openssl_decrypt(base64_decode($biz_data), 'AES-128-ECB',$aesKey,OPENSSL_RAW_DATA);
        $biz_data = json_decode($biz_data, true);
        return $biz_data;
    }

    /*
     * 响应
     * */
    public function response(string $status,string $msg,$data = ''){
        header('content-type:application/json;charset=utf-8');
        if(!empty($data) && is_array($data)){
            $data = json_encode($data);
        }
        $res = [
            'status' => $status,
            'msg' => $msg,
            'data' => $data
        ];
        die(json_encode($res));
    }


    /*
     * 调用巨省api  请求数据
     * suffix -- host(/xx/xx/xx) 括号里的
     * */
    public function getData($url_suffix,$data){
        $url = self::$jushengHost . $url_suffix;
        /*
         * 使用aesKet加密业务数据
         * */
        $aesKey = self::$aesKey;
        $publicKey = self::$jushengPubKey;

        $publicKey = "-----BEGIN PUBLIC KEY-----\n" .
            wordwrap($publicKey, 64, "\n", true) .
            "\n-----END PUBLIC KEY-----";
        openssl_public_encrypt($aesKey,$secretKey,$publicKey);
        $secretKey = base64_encode($secretKey);  // rsa加密过的对称秘钥

        $channelCode = self::$channelCode; // 渠道号
        $timestamp = time();  // 时间戳

        $data = json_encode($data);

        $biz_data =  openssl_encrypt($data, 'AES-128-ECB',$aesKey,OPENSSL_RAW_DATA);
        $biz_data = base64_encode($biz_data);  // 业务数据

        $params = [
            'secret_key' => $secretKey,
            'biz_data' => $biz_data,
            'channel_code' => $channelCode,
            'timestamp' => $timestamp
        ];

        $tmpStr = $this->getSignContent($params);
        $together = $tmpStr.self::$jushengMd5Key;
        $sign = md5($together);  // 这里可以大写,但没大写
        $params['sign'] = $sign;

        Helper::formatData($params, 2);
        $params = json_encode($params);
        return $this->postData($url, $params);
    }


    protected function getSignContent($params) {
        ksort($params);
        $stringToBeSigned = "";
        $i = 0;
        foreach ($params as $k => $v) {
            if ($i == 0) {
                $stringToBeSigned .= "$k" . "=" . $v;
            } else {
                $stringToBeSigned .= "&" . "$k" . "=" . $v;
            }
            $i++;
        }
        return $stringToBeSigned;
    }


    protected function postData($url, $data){
        $header = [
            'Content-type: application/json'
        ];
        //初始化
        $curl = curl_init();
        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);//post提交方式
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        $res = curl_exec($curl);
        curl_close($curl);

        // 处理响应
        $res = json_decode($res,true);

        if ($res['status'] === "0000"){  // 0000代表请求成功
            return json_decode($res['data'],true);
        }else{
            if(isset($res['msg']))  $this->response("0001",$res['msg']);
            $this->response(0,"三方api请求失败");
            return false;
        }
    }

}
