<?php

namespace api\controllers;

use common\Bridge;
use common\Config;
use common\Helper;
use common\models\Common;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use api\models\User;
use common\models\ArticleDcDescModel;
use common\models\ArticleModel;
use common\models\UserRelationshipModel;

class BridgeController extends CommonController
{
    public $modelClass = '';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
            'authMethods' => [
                HttpBearerAuth::className(),
                QueryParamAuth::className(),
            ],
            'except' => ['index', 'get-dc-detail', 'get-user-dc-level', 'binding-relationship', 'flash-login', 'flash-login-pro', 'flash-login-xijing', 'flash-login-big']
        ];
        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'],$actions['view']);
        return $actions;
    }


    /*
     * 获取用户的贷超信息
     * */
    public function actionGetUserDcLevel(){
        if($this->post('key') !== Bridge::SALT) return false;
        $bridge = new Bridge();
        $mobile = $this->post('mobile');
        $level = $bridge->getUserDcLevel($mobile);
        Bridge::returnJson(0,$level);
    }


    /*
     * 贷超详情页文章信息
     * */
    public function actionGetDcDetail(){
        $data = ArticleDcDescModel::find()->asArray()->all();
        foreach ($data as $k=>$v){
            switch ($v['type']){
                case 1:  // 文章
                    $article_id = $v['article_id'];
                    break;
                case 2:
                    $promote_way = $v['content'];
                    break;
            }
        }

        $promote_info = [
            'promote_way' => $promote_way
        ];

        $article_info = ArticleModel::find()->select('id,title,desc,preview_image')->where(['id'=>$article_id])->asArray()->one();
        if(!$article_info){
            $article_info['article_id'] = '';
            $article_info['title'] = '';
            $article_info['preview_image'] = '';
        }

        return array_merge($promote_info,$article_info);
    }


    /*
     *
     * */
    public function actionGetReportDetail(){
        $article_id = ArticleDcDescModel::find()->select('article_id')->where(['type' => 3])->scalar();
        $promote_way = ArticleDcDescModel::find()->select('content')->where(['type' => 4])->scalar();

        $promote_info = [
            'promote_way' => $promote_way
        ];

        $article_info = ArticleModel::find()->select('id,title,desc,preview_image')->where(['id'=>$article_id])->asArray()->one();
        if(!$article_info){
            $article_info['article_id'] = '';
            $article_info['title'] = '';
            $article_info['preview_image'] = '';
        }

        return array_merge($promote_info,$article_info);
    }


    /*
     * 绑定用户信息  1.html
     * */
    public function actionBindingRelationship(){
        if($this->post('key') !== Bridge::SALT) return false;
        $user_name = $this->post('user_name');
        $leader_mobile = $this->post('leader_mobile'); // 上家的手机号
        $mobile = $this->post('mobile'); // 需要注册的手机号,注册并绑定用户关系

        /*
         * 借钱人/下家
         * */
        $user = new User();
        $access_token = $res = $user->getAccessToken((int)$mobile); // 下家access_token
        $user = User::find()->where(['access_token' => $access_token['message']])->one();
        if($user_name && empty($user->nick_name)){
            $user->nick_name = $user_name;
            $user->save();
        }

        /*
         * 上家
         * */
        $leader_user = new User();
        $leader_access_token =  $leader_user->getAccessToken((int)$leader_mobile);
        $leader = User::find()->where(['access_token' => $leader_access_token['message']])->one();

        $is_bind = UserRelationshipModel::find()->where(['user_id' => $user->id])->one(); // 查询是否绑定过上家
        if(!$is_bind){
            $relationship = new UserRelationshipModel();
            if($leader->id == $user->id) return false; // 不能成为自己的下家
            $relationship->leader_id = $leader->id; // 上家id
            $relationship->user_id = $user->id; // 下家id
            $relationship->create_time = date('Y-m-d H:i:s');
            $relationship->save();

            /*
             * 查询上家有多少个下家用户,更改其级别 白银、黄金、钻石
             * */
            if(in_array($leader->id,Bridge::RELA_ID_WHITE_LIST)){
                Helper::pushMessage(5,['accept_user_id' => $leader->id,'content' => Config::TEAMMSG]);
                return true;
            }
            
            $leader_count = UserRelationshipModel::find()->where(['leader_id' => $leader->id])->count();
/*
            if($leader_count >= Bridge::GOLD_COUNT && $leader_count < Bridge::DIAMOND_COUNT){
                $leader->user_level = Bridge::GOLD;
                $leader->save();
            }elseif ($leader_count >= Bridge::DIAMOND_COUNT){
                $leader->user_level = Bridge::DIAMOND;
                $leader->save();
            }
*/

            if($leader_count >= Bridge::DIAMOND_COUNT){
                $leader->user_level = Bridge::DIAMOND;
                $leader->save();
            }
            Helper::pushMessage(5,['accept_user_id' => $leader->id,'content' => Config::TEAMMSG]);
        }

        return true;
    }

    // 闪登
    public function actionFlashLogin(){
        $cid = $this->post('cid');

        $postUrl = 'https://mobileauth.yunpian.com/api/auth/acquirePhone';
        $curlPost = ['cid' => $cid];

        $header = [
            'Content-Type: application/json',
            'x-app-id: 8f48bede7a0f4e3089f4e47cbd5a3db8',
            'x-app-key: dbf5351103464c4e9ac5bb076fce3891',
            'x-timestamp: ' . time() * 1000,
            'x-nonce: ' . session_create_id('')
        ];

        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($curlPost));
        $file_contents = curl_exec($ch);//运行curl
        curl_close($ch);

        if ($file_contents) {
            $response = json_decode($file_contents, true);

            if (isset($response['msg'])) {
                return Common::response(0, $response['msg'], []);
            } else if (isset($response['result'])) {
                $mobile = $response['result'];
            } else {
                return Common::response(0, '获取失败', []);
            }
        } else {
            return Common::response(0, '获取失败', []);
        }


        $user = new User();
        $access_token = $user->getAccessToken($mobile)['message'];
        $bridge = new Bridge();
        $xijin_loan_token = $bridge->fetchAccessToken($mobile);
        return [
            'xijin_token' => $access_token,
            'xijin_loan_token' => $xijin_loan_token,
            'mobile' => $mobile
        ];
    }

    // 闪登
    public function actionFlashLoginPro(){
        $cid = $this->post('cid');

        $postUrl = 'https://mobileauth.yunpian.com/api/auth/acquirePhone';
        $curlPost = ['cid' => $cid];

        $header = [
            'Content-Type: application/json',
            'x-app-id: f458b6c4a74544f4bd18b5c720a31327',
            'x-app-key: f5f760c1a74a437b841cc5c1bb72deeb',
            'x-timestamp: ' . time() * 1000,
            'x-nonce: ' . session_create_id('')
        ];

        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($curlPost));
        $file_contents = curl_exec($ch);//运行curl
        curl_close($ch);

        if ($file_contents) {
            $response = json_decode($file_contents, true);

            if (isset($response['msg'])) {
                return Common::response(0, $response['msg'], []);
            } else if (isset($response['result'])) {
                $mobile = $response['result'];
            } else {
                return Common::response(0, '获取失败', []);
            }
        } else {
            return Common::response(0, '获取失败', []);
        }


        $user = new User();
        $access_token = $user->getAccessToken($mobile)['message'];
        $bridge = new Bridge();
        $xijin_loan_token = $bridge->fetchAccessToken($mobile);
        return [
            'xijin_token' => $access_token,
            'xijin_loan_token' => $xijin_loan_token,
            'mobile' => $mobile
        ];
    }

    // 闪登
    public function actionFlashLoginBig(){
        $cid = $this->post('cid');

        $postUrl = 'https://mobileauth.yunpian.com/api/auth/acquirePhone';
        $curlPost = ['cid' => $cid];

        $header = [
            'Content-Type: application/json',
            'x-app-id: e40599be8f3b4d27b3d4e4f8669e6a11',
            'x-app-key: bfb18aba969749d88a8fa90b81bdb3c8',
            'x-timestamp: ' . time() * 1000,
            'x-nonce: ' . session_create_id('')
        ];

        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($curlPost));
        $file_contents = curl_exec($ch);//运行curl
        curl_close($ch);

        if ($file_contents) {
            $response = json_decode($file_contents, true);

            if (isset($response['msg'])) {
                return Common::response(0, $response['msg'], []);
            } else if (isset($response['result'])) {
                $mobile = $response['result'];
            } else {
                return Common::response(0, '获取失败', []);
            }
        } else {
            return Common::response(0, '获取失败', []);
        }


        $user = new User();
        $access_token = $user->getAccessToken($mobile)['message'];
        $bridge = new Bridge();
        $xijin_loan_token = $bridge->fetchAccessToken($mobile);
        return [
            'xijin_token' => $access_token,
            'xijin_loan_token' => $xijin_loan_token,
            'mobile' => $mobile
        ];
    }

        // 闪登
        public function actionFlashLoginXijing(){
            $cid = $this->post('cid');
    
            $postUrl = 'https://mobileauth.yunpian.com/api/auth/acquirePhone';
            $curlPost = ['cid' => $cid];
    
            $header = [
                'Content-Type: application/json',
                'x-app-id: 2935d5bbf92844e2bafb12640b22eaa1',
                'x-app-key: 32a6dbcb17024f78984c90dd63769cea',
                'x-timestamp: ' . time() * 1000,
                'x-nonce: ' . session_create_id('')
            ];
    
            $ch = curl_init();//初始化curl
            curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
            curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($curlPost));
            $file_contents = curl_exec($ch);//运行curl
            curl_close($ch);
    
            if ($file_contents) {
                $response = json_decode($file_contents, true);
    
                if (isset($response['msg'])) {
                    return Common::response(0, $response['msg'], []);
                } else if (isset($response['result'])) {
                    $mobile = $response['result'];
                } else {
                    return Common::response(0, '获取失败', []);
                }
            } else {
                return Common::response(0, '获取失败', []);
            }
    
            $user = new User();
            $access_token = $user->getAccessToken($mobile)['message'];
            $bridge = new Bridge();
            $xijin_loan_token = $bridge->fetchAccessToken($mobile);
            return [
                'xijin_token' => $access_token,
                'xijin_loan_token' => $xijin_loan_token,
                'mobile' => $mobile
            ];
        }

}
















