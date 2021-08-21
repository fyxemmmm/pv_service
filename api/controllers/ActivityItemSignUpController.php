<?php

namespace api\controllers;

use api\models\Activity;
use api\models\ActivityItemSignUp;
use common\models\ActivityItemSignUpModel;
use Yii;
use api\models\ActivityItemSignUpSearch;
use yii\web\NotFoundHttpException;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\QueryParamAuth;
use yii\filters\auth\HttpBearerAuth;
use common\models\Common;

/**
 * ArticleController implements the CRUD actions for Article model.
 */
class ActivityItemSignUpController extends CommonController
{
    public $modelClass = '';

//    public function behaviors()
//    {
//        $behaviors = parent::behaviors();
//        $behaviors['authenticator'] = [
//            'class' => CompositeAuth::className(),
//            'authMethods' => [
//                HttpBearerAuth::className(),
//                QueryParamAuth::className(),
//            ],
//            'except' => ['index','view']
//        ];
//        return $behaviors;
//    }


    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'],$actions['view']);
        return $actions;
    }


    /**
     * Lists all sign models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ActivityItemSignUpSearch();
        $queryParams = Yii::$app->request->queryParams;
        $search['ActivityItemSignUpSearch'] = $queryParams;
        $dataProvider = $searchModel->search($search);
        return $dataProvider;
    }


    /**
     * Displays a single sign model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $searchModel = new ActivityItemSignUpSearch();
        $queryParams = Yii::$app->request->queryParams;
        $search['ActivityItemSignUpSearch'] = $queryParams;
        $dataProvider = $searchModel->search($search);
        return $dataProvider;
    }

    /**
     * Creates a new sign model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */

    public function actionCreate()
    {
        $model = new ActivityItemSignUpModel();
        $active_id = $this->post('activity_id');
        $mobile = $this->post('mobile') ?? '';
        $name = $this->post('name') ?? '';
        $price = $this->post('price') ?? '';

        $user_id = $this->userId;
        $values = [
            'activity_id' => $active_id,
            'mobile' => (string)$mobile,
            'user_name' => $name,
            'user_id' => $user_id,
            'price' => $price
        ];
        $model->attributes = $values;
        $model->save();
        return common::response(1, '添加成功',$model);
    }

    /**
     * Updates an existing sign model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
//        $queryParams = Yii::$app->request->queryParams;

//        return Common::response(1, '修改成功',['active'=>$active]);
    }

    /**
     * Deletes an existing sign model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }


    // 检测是否该用户已经报名过这个活动
    public function actionCheckRegister(){
        $activity_id = $this->post('activity_id');
        $data = ActivityItemSignUp::find()->where(['activity_id' => $activity_id])->andWhere(['user_id' => $this->userId])->one();
        if($data) return Common::response(0, '已经报名过该项目',['is_register' => true]);
        return Common::response(1, '未报名过该项目', ['is_register' => false]);
    }

    // 我的门票
    public function actionMyTicket(){
        $user_id = $this->userId;
        $fields = "activity.*,B.mobile,B.user_name";
        $activity_id = $this->get('activity') ?? 0;
        $data = Activity::find()->leftJoin('activity_item_sign_up as B','activity.id = B.activity_id')
                                ->select($fields)
                                ->where(['B.activity_id' => $activity_id, 'B.user_id' => $user_id])
                                ->asArray()
                                ->one();
        unset($data['registration_deadline'],$data['user_id'],$data['content']);
//        foreach ($data as $k=>&$v){
//            if($k == 'activity_time' || $k == 'activity_time_end'){
//                $v = date('Y-m-d',strtotime($v));
//            }
//        }
        if($data) return Common::response(1, '获取成功',$data);
        return Common::response(0, '获取失败');
    }

    /**
     * Finds the Article model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ActivityItemSignUpModel the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ActivityItemSignUpModel::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
