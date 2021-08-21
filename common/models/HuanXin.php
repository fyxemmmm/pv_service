<?php
namespace common\models;

use Yii;
use common\models\Common;

class HuanXin
{
    public $orgname;
    public $appKey;
    public $clientSecret;
    public $appName;
    public $clientID;
    public $url = "http://a1.easemob.com";
    public $baseUrl = '';

    public function __construct($orgname, $appKey, $appName, $clientID, $clientSecret)
    {
        $this->orgname = $orgname;
        $this->appKey = $appKey;
        $this->appName = $appName;
        $this->clientID = $clientID;
        $this->clientSecret = $clientSecret;
        $this->main();
        $this->getToken();
    }

    private function main()
    {
        $baseUrl = $this->url . '/' . $this->orgname . '/' . $this->appName . '/';
        $this->baseUrl = $baseUrl;
        $this->tokenUrl = $baseUrl . 'token';
        $this->registerUserUrl = $baseUrl . 'users';
    }

    public function getToken()
    {
        $cache = Yii::$app->cache;
        $huanxin_token = $cache->get('huanxin_token');
        if ($huanxin_token) return true;
        $tokenUrl = $this->tokenUrl;
        $data = [
            "grant_type" => "client_credentials",
            "client_id" => $this->clientID,
            "client_secret" => $this->clientSecret
        ];
        $data = json_encode($data);
        $result = json_decode($this->requestPost($tokenUrl, $data));
        if ($result) {
            $cache->set('huanxin_token', $result->access_token, $result->expires_in - 100);
            $cache->set('huanxin_application', $result->application, $result->expires_in - 100);
            return true;
        }
        return false;
    }

    public function registerUser($username, $password, $nickname = '')
    {
        $registerUserUrl = $this->registerUserUrl;
        $cache = Yii::$app->cache;
        $huanxin_token = $cache->get('huanxin_token');
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: Bearer ' . $huanxin_token;
        $data = [
            'username' => $username,
            'password' => $password,
            'nickname' => $nickname
        ];
        $data = json_encode($data);
        $result = json_decode($this->requestPost($registerUserUrl, $data, $headers));
        return $result;
    }

    public function getUserInfo($user_name){
        $cache = Yii::$app->cache;
        $huanxin_token = $cache->get('huanxin_token');
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: Bearer ' . $huanxin_token;

        $url = $this->baseUrl . 'users/' . $user_name;
        $res = $this->requestGet($url, $headers);
        return $res;
//        var_dump($res);exit;
    }

    /**
     * 发起get请求
     *
     * @param string $url
     * @param string $param
     * @return object $data
     */
    private function requestGet($url = '',  $headers = []) {
        $getUrl = $url;
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL,$getUrl);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        $data = curl_exec($ch);//运行curl
        curl_close($ch);
        return $data;
    }

    /**
     * 发起post请求
     *
     * @param string $url
     * @param string $param
     * @return object $data
     */
    private function requestPost($url = '', $param = '', $headers = []) {
        if (empty($url) || empty($param)) {
            return false;
        }
        $postUrl = $url;
        $curlPost = $param;
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch);//运行curl
        curl_close($ch);
        return $data;
    }
}
