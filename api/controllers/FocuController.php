<?php

namespace api\controllers;

use common\Helper;
use common\models\UserModel;
use Yii;
use common\models\FocusModel;
use api\models\FocusSearch;
use yii\web\NotFoundHttpException;
use common\models\Common;
use api\models\Focus;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\QueryParamAuth;
use yii\filters\auth\HttpBearerAuth;

/**
 * FocuController implements the CRUD actions for FocusModel model.
 */
class FocuController extends CommonController
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
            'except' => ['fan-focus']
        ];
        return $behaviors;
    }


    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'], $actions['view']);
        return $actions;
    }

    public function actionNewFansList(){
        $page = (int)($this->get('page') ?? 1);
        $per_page = (int)($this->get('per-page') ?? 20);
        if($per_page === 0) $per_page = 1;
        $user_id = $this->userId;
        $fans_list = Focus::find()->where(['focus_user_id' => $user_id])->orderBy('id desc');
        $totalCount = (int)$fans_list->count();

        $page_count = ceil($totalCount / $per_page);

        if($per_page * $page > $totalCount){
            $page = $page_count;
        }

        $fans_list = $fans_list->offset($page-1)
                               ->limit($per_page)
                               ->all();
        // 变成已读状态
        FocusModel::updateAll(['read' => 1], ['read' => 0,'focus_user_id' => $user_id]);
        $response = [
            'items' => $fans_list,
            '_meta' => [
                'totalCount'=>$totalCount,'pageCount'=>$page_count,'currentPage'=>$page,'perPage'=>$per_page
            ]
        ];

        return Common::response(1, '获取成功', $response);
    }

    // 我的关注 / 他的关注
    public function actionFanFocus(){
        $user_id = $this->get('id'); // 用户的id
        $type = $this->get('type'); // 1 关注    2 粉丝
        if($type == 1){
            $model = Focus::find()->where(['user_id' => $user_id])->orderBy('id desc');
        }else{
            $model = Focus::find()->where(['focus_user_id' => $user_id])->orderBy('id desc');
        }
        $res = Helper::usePage($model);
        foreach ($res['items'] as $k=>&$v){
            if($type == 1){
                $v['nick_name'] = UserModel::find()->select('nick_name')->where(['id' => $v['focus_user_id']])->scalar() ?: '';
                $v['avatar_image'] = UserModel::find()->select('avatar_image')->where(['id' => $v['focus_user_id']])->scalar() ?: '';
                $f_info = Focus::find()->where(['user_id' => $this->userId, 'focus_user_id' => $v['focus_user_id']])->one();
                $v['is_focus'] = $f_info ? '1' : '0';
                $v['user_id'] = $v['focus_user_id'];
            }else{
                $v['nick_name'] = UserModel::find()->select('nick_name')->where(['id' => $v['user_id']])->scalar() ?: '';
                $v['avatar_image'] = UserModel::find()->select('avatar_image')->where(['id' => $v['user_id']])->scalar() ?: '';
                $f_info = Focus::find()->where(['user_id' => $this->userId, 'focus_user_id' => $v['user_id']])->one();
                $v['is_focus'] = $f_info ? '1' : '0';
            }
            unset($v['focus_user_id']);
            unset($v['read']);
            unset($v['id']);
        }
        return $res;
    }

    // 我的关注 / 他的关注
    public function actionMyFocus(){
        $user_info = $this->getUserInfo();
        $user_id = $user_info['id'];

        $list = Focus::find()->where(['user_id' => $user_id])->orderBy('id desc')->asArray()->all();
        foreach ($list as $k=>&$v){
            $v['nick_name'] = UserModel::find()->select('nick_name')->where(['id' => $v['focus_user_id']])->scalar() ?: '';
            $v['avatar_image'] = UserModel::find()->select('avatar_image')->where(['id' => $v['focus_user_id']])->scalar() ?: '';
            $v['huanxin_username'] = UserModel::find()->select('huanxin_username')->where(['id' => $v['focus_user_id']])->scalar() ?: '';
            $v['user_id'] = $v['focus_user_id'];
            unset($v['focus_user_id']);
            unset($v['read']);
            unset($v['id']);
        }
        return $list;
    }

    /**
     * Lists all FocusModel models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new FocusSearch();
        $queryParams = Yii::$app->request->queryParams;
        $search['FocusSearch'] = $queryParams;
        $dataProvider = $searchModel->search($search);
        return $dataProvider;
    }

    /**
     * Displays a single FocusModel model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $searchModel = new FocusSearch();
        $queryParams = Yii::$app->request->queryParams;
        $search['FocusSearch'] = $queryParams;
        $dataProvider = $searchModel->search($search);
        return $dataProvider;
    }


    /**
     * Finds the FocusModel model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return FocusModel the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = FocusModel::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
