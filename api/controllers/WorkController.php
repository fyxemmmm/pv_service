<?php

namespace api\controllers;

use api\models\UploadForm;
use api\models\User;
use common\models\Common;
use common\models\WorkResumeModel;
use Yii;
use common\models\WorkModel;
use api\models\WorkSearch;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

/**
 * WorkController implements the CRUD actions for WorkModel model.
 */
class WorkController extends CommonController
{
    public $modelClass = 'common\models\WorkModel';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
            'authMethods' => [
                HttpBearerAuth::className(),
                QueryParamAuth::className(),
            ],
            'except' => ['index', 'view', 'check-uploaded']
        ];
        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index']);
        return $actions;
    }

    /**
     * Lists all WorkModel models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new WorkSearch();
        $search = ['WorkSearch' => Yii::$app->request->queryParams];
        $dataProvider = $searchModel->search($search);

        return $dataProvider;
    }

    /**
     * Creates a new WorkModel model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new WorkModel();

        $attributes = Yii::$app->request->post();
        $user_info = $this->getUserInfo();
        $user_id = $user_info['id'];

        $attributes['user_id'] = $user_id;
        $model->setAttributes($attributes);

        if ($model->save()) return Common::response(1, '创建成功', $model);
        return Common::response(0, '创建失败', $model->getErrors());
    }

    /**
     * Updates an existing WorkModel model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing WorkModel model.
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
     * Finds the WorkModel model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return WorkModel the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = WorkModel::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionUploadResume()
    {
        $work_id = $this->post('work_id', 0);

	    if (!$_FILES) return Common::response(0, '请选择文件');
        $file = $_FILES['uploadFile'];
        if (!$file || $file['error'] !== 0) return Common::response(0, '请选择文件');
        if ($file['size'] > 1024 * 1024 * 10) return Common::response(1, '文件大小不能超过10MB');

        $date = Common::generateDatetime('Y-m-d');
        $file_temp = $file['tmp_name'];
        $file_oss_path = $date . '/' . $file['name'];

        $ret = Common::uploadToAliyun_oss('', $file_temp, $file_oss_path);
        $url = $ret['info']['url'] ?? '';

        // 文件上传成功
        $user_info = $this->getUserInfo();
        $user_id = $user_info ? $user_info['id'] : 0;

        $data = [
            'user_id' => $user_id,
            'work_id' => $work_id,
            'resume_path' => $url
        ];

        $WorkResumeModel = WorkResumeModel::findOne(['user_id' => $user_id, 'work_id' => $work_id]);
        if (!$WorkResumeModel) {
            $WorkResumeModel = new WorkResumeModel();
        }

        $WorkResumeModel->setAttributes($data);
        $WorkResumeModel->save();

        return Common::response(1, '上传成功');
    }

    public function actionCheckUploaded()
    {
        $work_id = $this->get('work_id', 0);

        $user_info = $this->getUserInfo();
        $user_id = $user_info ? $user_info['id'] : 0;

        $WorkResumeModel = WorkResumeModel::findOne(['user_id' => $user_id, 'work_id' => $work_id]);
        $status = $WorkResumeModel ? 1 : 0;

        return Common::response(1, 'Success', ['uploaded' => $status]);
    }

}
