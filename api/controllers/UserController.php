<?php

namespace api\controllers;

use common\Bridge;
use common\Config;
use common\Helper;
use common\models\JushengVipModel;
use common\models\UmengPush;
use common\models\UserModel;
use common\models\UserRelationshipModel;
use Yii;
use api\controllers\CommonController;
use api\models\User;
use api\models\UserSearch;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use common\models\Common;
use common\models\ArticleCollectModel;
use common\models\ArticleBrowseModel;
use common\models\CheckOpenLogic;

class UserController extends CommonController
{
    public $modelClass = 'api\models\User';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
            'authMethods' => [
                HttpBearerAuth::className(),
                QueryParamAuth::className(),
            ],
            'except' => ['get-access-token', 'get-sms-code', 'send-sms-code', 'get-access-token-tpp', 'check-bind', 'check-mobile', 'index', 'view', 'click-farm', 'union-register', 'bind-relationship', 'acquire-token', 'customer-service-url', 'question-url','xmob-type','question-url-xijin'],
        ];
        return $behaviors;
    }
    
    public function actions()
    {
        $actions = parent::actions();
        unset( $actions['update'], $actions['index']);
        return $actions;
    }

    public function beforeAction($action)
    {
        parent::beforeAction($action);
        $notAllow = ['delete', 'create'];
        if (in_array($this->action_id, $notAllow)) {
            return Common::customzieError("没有权限", '0', 405);
        }
        return true;
    }

    public function actionIndex(){
        $searchModel = new UserSearch();
        $queryParams = Yii::$app->request->queryParams;
        $search['UserSearch'] = $queryParams;
        $dataProvider = $searchModel->search($search);
        return $dataProvider;
    }

    public function actionUpdate(){
        $user = UserSearch::findOne($this->userId);
        $bodyParams = Yii::$app->request->post();
        $user->scenario = 'update';
        $bodyParams['avatar_image'] = $bodyParams['avatar_image'] ?? $user->avatar_image ?? 'https://xijin.oss-cn-shanghai.aliyuncs.com/static/imgs/%E5%BE%AE%E4%BF%A1%E5%9B%BE%E7%89%87_20191105125332.png';
        $user->setAttributes($bodyParams);
        if ($user->save()) return Common::response(1, '更新成功', $user);
        return Common::response(0, '失败', $user->getErrors());
    }

    /**
 * 获取token
 *
 * @return string $access_token
 */
    public function actionGetAccessToken()
    {
        (int)$mobile = $this->post('mobile');
        $smsCode = $this->post('sms_code');
        $channel_id = $this->post('channel_id');

        $test_arr = [
            13564658832,
            15901725624,
            15755168442,
            17501665943,
            13701874183,
            18501704826,
            13922856887,
            18616731161,
            15601897815,
            13120787888
        ];

        // 测试账号特殊处理
        if (in_array($mobile, $test_arr)) {
            $user = new User();
            $res = $user->getAccessToken($mobile);
            return Common::response($res['status'],  $res['message'], ['access_token' => $res['message']]);
        }

        $res = $this->checkMobile($mobile, $smsCode);
        if (! $res['status']) return Common::response($res['status'], $res['message']);

        $user = new User();
        $res = $user->getAccessToken($mobile ,$channel_id);
        return Common::response($res['status'],  $res['message'], ['access_token' => $res['message']]);
    }

    /**
     * Xmob-H5接口
     * @return  $file_contents
     */
    public function actionXmobType()
    {
        //点击数据ID，后续转化数据匹配到点击数据后，按照此ID回传数据
        $mob_cid = $this->post('mob_cid');
        //广告单元ID
        $plid = $this->post('plid');
        //转化类型，注册0，激活1，会员购买2
        $type = 0;
        //回调url
        //$url = 'https://www.lnkdata.com/UrlCenter/Osh?menu_id=800003&mob_cid='.$mob_cid.'&plid='.$plid.'&type='.$type;
        //$file_contents = file_get_contents($url);
        //return $file_contents;
        /*
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $result = curl_exec($ch);
        curl_close ($ch);
        return $result;
        */
    }

    public function actionAcquireToken()
    {
        $cid = $this->post('cid');
        $channel_id = $this->post('channel_id');

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
        $res = $user->getAccessToken($mobile, $channel_id);
        return Common::response($res['status'],  $res['message'], ['access_token' => $res['message'], 'mobile' => $mobile]);
    }

    public function actionCheckMobile()
    {
        (int)$mobile = $this->post('mobile');
        $smsCode = $this->post('sms_code');
        $res = $this->checkMobile($mobile, $smsCode);
        if (! $res['status']) return Common::response($res['status'], $res['message']);
        return Common::response(1, 'success');
    }

    private function checkMobile($mobile, $smsCode)
    {
        if (!Common::verifiyMobile($mobile)) return Common::messageReturn(0, '手机格式不对');
        if (strlen($smsCode) != 4) return Common::messageReturn(0, '验证码格式不对');
        $verifiySms = Common::toggleSmsCode($mobile);
        if (!$verifiySms) return Common::messageReturn(0, "失败, 请重新获取");
        if ($verifiySms != $smsCode) return Common::messageReturn(0, "验证码有误");
        return common::messageReturn(1);
    }

    public function actionGetAccessTokenTpp()
    {
        $wechat_token = $this->post('wechat_token');
        $qq_token = $this->post('qq_token');

        if (!$wechat_token && !$qq_token) {
            return Common::response(0, '参数错误');
        }

        $mobile = User::find()
            ->select('mobile')
            ->filterWhere([
                'wechat_token' => $wechat_token,
                'qq_token' => $qq_token
            ])
            ->scalar();

        if ($mobile) {
            $user = new User();
            $res = $user->getAccessToken($mobile);
            return Common::response($res['status'],  $res['message'], ['access_token' => $res['message']]);
        } else {
            return Common::response(0,  '用户不存在', ['access_token' => '']);
        }
    }

    /**
     * 获取验证码
     *
     * @return string $access_token
     */
    public function actionGetSmsCode()
    {
        //xijing阿里云 非xijing 啸鹰
        $headers = Yii::$app->request->headers;
        $name = $headers->get('name');
        (int)$mobile = $this->post('mobile');
        if($name == 'xijing'){
            if ($mobile == "15061690110") {
                $cache = Yii::$app->cache;
                $smsCode = 1234;
                $cache->set('sms_code_' . $mobile, $smsCode, 60 * 10);
                return Common::response(1, '获取成功');
            }
            if (!Common::verifiyMobile($mobile)) return Common::response(0, '手机格式不对');
            $smsCode = rand(1000, 9999);
            $res = Common::toggleSmsCode($mobile, $smsCode);
        }else{
            if ($mobile == "15061690110") {
                $cache = Yii::$app->cache;
                $smsCode = 1234;
                $cache->set('sms_code_' . $mobile, $smsCode, 60 * 10);
                return Common::response(1, '获取成功');
            }
            if (!Common::verifiyMobile($mobile)) return Common::response(0, '手机格式不对');
            $smsCode = rand(1000, 9999);
            $res = Common::yunpianSmsCode($mobile, $smsCode);
        }
        if ($res) return Common::response($res['status'], $res['message'], $res['info']);
        return Common::response(1, '获取成功');
    }

    public function actionInfo()
    {
        $user = $this->getUserInfo();
        if ($user){
            $res = [];
            foreach ($user as $k=>$v){
                if($k == 'password') continue;
                $res[$k] = $v;
            }
            $vip_model = JushengVipModel::find()->select('vip_type,expire_time as vip_expire_time')->where(['user_id' => $user->id])->asArray()->one();
            if(!$vip_model){
                $res['vip_type'] = "0";
                $res['vip_expire_time'] = "";
            }else{
                $res['vip_type'] = $vip_model['vip_type'];
                $res['vip_expire_time'] = $vip_model['vip_expire_time'];
            }
            return Common::response(1, '请求成功', $res);
        }
        return Common::response(0, '用户不存在或已禁用', '', 404);
    }

    /**
     *
     * @return string $access_token
     */
    public function actionSendSmsCode()
    {
        // $referrer = Yii::$app->request->referrer;
        // $referrer_host = split('?', $referrer)[0];
        // if (! in_array($referrer_host, ['http'])) return Common::response(0, 'error');
        
        (int)$mobile = $this->post('mobile');
        if (!Common::verifiyMobile($mobile)) return Common::response(0, '手机格式不对');
        $smsCode = rand(1000, 9999);
        $res = Common::toggleSmsCode($mobile, $smsCode);
        if ($res) return Common::response($res['status'], $res['message'], $res['info']);
        return Common::response(1, '获取成功');
    }

    public function actionMyCollect()
    {
        $collect = ArticleCollectModel::find()
            ->from('article_collect as ac')
            ->select(['title' => 'a.title', 'preview_image' => 'a.preview_image', 'desc' => 'a.desc', 'id' => 'a.id'])
            ->leftJoin('article a', 'ac.article_id=a.id')
            ->where("ac.user_id={$this->userId}")
            ->asArray()
            ->all();

        return Common::response(1, '', $collect);
    }

    public function actionMyBrowseRecord()
    {
        $perPage = $this->get('per-page', 20);
        $page = $this->get('page', 1);

        $obj = ArticleBrowseModel::find()
            ->from('article_browse as ab')
            ->select(['article_id' => 'a.id', 'title' => 'a.title', 'preview_image' => 'a.preview_image', 'desc' => 'a.desc', 'a.like_num', 'a.comment_num', 'my_view_count' => 'COUNT(1)', 'create_time' => 'MAX(ab.create_time)'])
            ->leftJoin('article a', 'ab.article_id=a.id')
            ->where("ab.user_id={$this->userId}")
            ->groupBy('a.id');

        $count = $obj->count();
        $pageCount = ceil($count/$perPage);
        $offset = ($page - 1) * $perPage;

        $article_browse = $obj
            ->orderBy('create_time desc')
            ->offset($offset)
            ->limit($perPage)
            ->asArray()
            ->all();

        $response = [
            'items' => $article_browse,
            '_meta' => [
                'totalCount' => $count,
                'pageCount' => $pageCount,
                'currentPage' => $page,
                'perPage' => $perPage
            ]
        ];

        return Common::response(1, '', $response);
    }

    public function actionCheckBind(){
        $wechat_token = $this->get('wechat_token');
        $qq_token = $this->get('qq_token');
        if ($wechat_token){
            $where = ['wechat_token' => $wechat_token];
        } else if($qq_token){
            $where = ['qq_token' => $qq_token];
        } else{
            return common::response(0, '参数缺失');
        }
        $user = User::find()
            ->where($where)
            ->one();
        if ($user) {
            Yii::$app->params['__web'] = [
                'user_id' => $user->id,
            ];
            return common::response(1, '已绑定', $user);
        }
        return Common::response(0, '未绑定');
    }

    public function actionClickFarm()
    {
        $mobile = $this->post("mobile");
        $platform = $this->post('platform');
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, 'https://api.loan.xykj1.com/click-farm');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query(['mobile' => $mobile, 'platform' => $platform]));
        $res = curl_exec($curl);
        curl_close($curl);

        die($res);
    }


    /*
     * 检查绑定, 没有绑定则会进行绑定(也会注册用户)
     * */
    public function actionBindRelationship(){
        $sign = $this->post('sign'); // 绑定用户关系的标识
        $mobile = $this->post('mobile'); // 下家手机号
        $leader_id = Bridge::decodeSign($sign);
        // 如果不正确的邀请码是不会通过关系绑定的
        if($leader_id === false) return Common::response(0, '您输入的推荐码无效，请确认推荐码输入正确。');

        $is_leader_exist = UserModel::findOne($leader_id); // 该上家用户是否存在
        // 虽然解析出来了用户id,但不能保证是否存在该用户 二次判断
        if(!$is_leader_exist) return Common::response(0, '该邀请码有误,未找到上级用户的相关信息');

        $user = new User();
        $current_user = $user->getAccessToken($mobile);  // 不存在就注册
        $user = User::find()->where(['access_token' => $current_user['message']])->one();

        $is_bind = UserRelationshipModel::find()->where(['user_id' => $user->id])->one();
        if($is_bind) return Common::response(1, "您已经绑定过用户",(string)$is_bind->leader_id);

        if($user->id == $leader_id) return Common::response(0, '绑定失败 不可成为自己的下家');

        $relationship_model = new UserRelationshipModel();
        $relationship_model->user_id = $user->id;
        $relationship_model->leader_id = $leader_id;
        $relationship_model->create_time = date('Y-m-d H:i:s');
        if($relationship_model->save()){
            if(in_array($leader_id,Bridge::RELA_ID_WHITE_LIST)){
                Helper::pushMessage(5,['accept_user_id' => $leader_id,'content' => Config::TEAMMSG]);
                return Common::response(1, '绑定成功');
            }
            $leader = User::findOne($leader_id);
            $leader_count = UserRelationshipModel::find()->where(['leader_id' => $leader_id])->count();
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

            Helper::pushMessage(5,['accept_user_id' => $leader_id,'content' => Config::TEAMMSG]);

            return Common::response(1, '绑定成功');
        }
        return Common::response(0, '绑定失败', $relationship_model->getErrors());
    }

    public function actionCustomerServiceUrl()
    {
        $value = Common::getAppSwitchKey('APP_CUSTOMER_SERVICE');
        $user_info = $this->getUserInfo();
        $nick_name = urlencode($user_info['nick_name']);
        $mobile = $user_info['mobile'];
        $id = $user_info['id'];
        return $value . "?nick_name=$nick_name&mobile=$mobile&id=$id";
    }

    public function actionQuestionUrl()
    {
        $CheckOpenLogic = new CheckOpenLogic();
        $open = $CheckOpenLogic->check();

        if ($open) {
            $value = Common::getAppSwitchKey('APP_QUESTION_LOAN');
        } else {
            $value = Common::getAppSwitchKey('APP_QUESTION');
        }

        return $value;
    }

    public function actionQuestionUrlXijin()
    {
        $value = Common::getAppSwitchKey('APP_QUESTION_LOAN_XIJIN');
        return $value;
    }

}
