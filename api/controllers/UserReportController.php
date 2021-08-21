<?php

namespace api\controllers;

use api\models\UserReport;
use Yii;
use api\models\UserReportSearch;
use yii\web\NotFoundHttpException;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\QueryParamAuth;
use yii\filters\auth\HttpBearerAuth;
use common\models\Common;

/**
 * ArticleController implements the CRUD actions for Article model.
 */
class UserReportController extends CommonController
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
     * Lists all Article models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new UserReportSearch();
        $queryParams = Yii::$app->request->queryParams;
        $search['UserReportSearch'] = $queryParams;
        $dataProvider = $searchModel->search($search);
        return $dataProvider;
    }


    /**
     * Displays a single Article model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        echo 0;exit;
    }

    /**
     * Creates a new Article model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        // 被举报用户的id
        $to_uid = $this->post('to_uid', 0);
        // 圈子id
        $mkg_id = $this->post('mkg_id', 0);
        $content = $this->post('content', '');
        $model = new UserReport();
        $data = [
            'user_id' => $this->userId,
            'to_uid' => $to_uid,
            'mkg_id' => $mkg_id,
            'content' => $content
        ];
        $model->attributes = $data;
        $model->save();
        return Common::response(1,  '操作成功');
    }

    /**
     * Updates an existing Article model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        exit;
    }

    /**
     * Deletes an existing Article model.
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

    /**
     * Finds the Article model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Article the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Article::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
