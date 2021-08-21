<?php

namespace service\controllers;

use common\models\AdminModel;
use Yii;
use service\controllers\CommonController;
use yii\data\ActiveDataProvider;
use service\models\Admin;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use common\models\Common;
use common\models\MenuModel;
use common\models\UserModel;

class AdminController extends CommonController
{
    public $modelClass = 'service\models\Admin';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
            'authMethods' => [
                HttpBearerAuth::className(),
                QueryParamAuth::className(),
            ],
            'except' => ['get-access-token', 'send-sms-code'],
        ];
        return $behaviors;
    }

    public function beforeAction($action)
    {
        parent::beforeAction($action);
        $notAllow = [];
        if (in_array($this->action_id, $notAllow)) {
            return Common::customzieError("没有权限", 0, 405);
        }
        return true;
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index']);
        return $actions;
    }

    /**
     * 获取token
     *
     * @return string $access_token
     */
    public function actionGetAccessToken()
    {
        $name = $this->post('name');
        $password = $this->post('sms_code');
        if (strlen($name) < 4) return Common::response(0, '账号不能少于4位');
//        if (strlen($password) < 6) return Common::response(0, '密码不能少于6位');
        $admin = new Admin();
        $res = $admin->getAccessToken($name, $password);
        if (! $res['status']) return Common::response(0, $res['message']);
        return Common::response(1, 'success', $res['info']);
    }

    public function actionSendSmsCode(){
        $mobile = $this->post('mobile');
        if (!Common::verifiyMobile($mobile)) return Common::response(0, '手机格式不对');
        $smsCode = rand(1000, 9999);
        $admin = new Admin();
        $res = $admin->setAdminSms($mobile,$smsCode);
        if($res) return Common::response(0, $res['message']);
        return Common::response(1, '获取成功');
    }

    /**
     * 获取用户信息 function
     *
     * @return void
     */
    public function actionInfo()
    {
        $authorization = Yii::$app->request->headers['authorization'];

        try{
            $access_token = explode(" ", $authorization)[1];
        }catch (\Exception $e){
            $access_token = null;
        }

        return Admin::find()->where(['access_token' => $access_token])->asArray()->all();
    }
    
    public function actionMenu()
    {
        $adminId = $this->adminId;
        $menus = MenuModel::find(['status' => 1])
            ->orderBy('sort')
            ->asArray()
            ->all();
        $arr = [];
        foreach ($menus as $k => $menu) {
            if ($menu['pid'] == 0) {
                array_push($arr, $menu);
                unset($menus[$k]);
            }
        }
        foreach ($menus as $key => $menu) {
            foreach($arr as $k => $v) {
                if ($menu['pid'] == $v['id']) {
                    $arr[$k]['subs'] = $arr[$k]['subs'] ?? [];
                    array_push($arr[$k]['subs'], $menu);
                    unset($menus[$key]);
                }
            }
        }
        return Common::response(1, 'success', $arr);
    }

    public function actionIndex() 
    {
        $query = Admin::find();
        if ($this->get('type')) $this->filterWhere($query, ['type' => $this->get('type')]);
        if ($this->get('status')) $this->filterWhere($query, ['status' => $this->get('status')]);
        return new ActiveDataProvider([
            'query' => $query,
        ]);
    }

    public function actionDelete($id)
    {
        $res = Admin::updateAll(['status' => 0], ['id' => $id]);
        if ($res) return Common::response(1, '删除成功');
        return Common::response(0, '删除失败');
    }

    public function actionCreate()
    {
        $data = [];
        $data['Admin'] = $this->post();
        $password = $this->post('password');
        $username = $this->post('name');
        if (strlen($password) < 6) return Common::response(0, '密码不能少于6位');
        if (AdminModel::findOne(['name' => $username])) return Common::response(0, '用户名已存在');
        $admin = new Admin();
        $res = $admin->createAdmin($data, $password);
        return Common::response($res['status'], $res['message'], $res['info']);
    }

    public function actionUpdate($id)
    {
        $data = [];
        $data['Admin'] = $this->post();
        $password = $this->post('password');
        $username = $this->post('name');
        if (strlen($password) < 6 && $password) return Common::response(0, '密码不能少于6位');
        $admin = Admin::findOne(['id' => $id]);
        if ($username != $admin->name && AdminModel::findOne(['name' => $username])) return Common::response(0, '用户名已存在');
        $admin->load($data);
        if ($password) $admin->password = Yii::$app->security->generatePasswordHash($password);
        if ($admin->save()) return Common::response(1, '更新成功');
        return Common::response(0, '更新失败', $admin->getErrors());
    }

}
