<?php

namespace api\controllers;

use common\models\RadioModel;
use common\models\RadioShaftModel;
use common\models\RadioCommentModel;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use api\models\RadioSearch;
use api\models\Radio;
use common\Helper;
use Yii;

// 电台
class RadioController extends CommonController
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
            'except' => ['index','view','new','get-count', 'new-info']
        ];
        return $behaviors;
    }

    public function actions()
    {
        Radio::$userId = $this->userId;
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'],$actions['view']);
        return $actions;
    }


    public function actionIndex()
    {
        RadioSearch::$hidden = true;
        $searchModel = new RadioSearch();
        $queryParams = Yii::$app->request->queryParams;
        $search['RadioSearch'] = $queryParams;
        $dataProvider = $searchModel->search($search);
        return $dataProvider;
    }

    // 电台详情
    public function actionView($id)
    {
        $searchModel = new RadioSearch();
        $queryParams = Yii::$app->request->queryParams;
        $search['RadioSearch'] = $queryParams;
        $dataProvider = $searchModel->search($search);
        return $dataProvider;
    }


    public function actionNew()
    {
        $info = RadioModel::find()
            ->select('id,preview_image')
            ->where(['status' => 1])
            ->orderBy('id desc')
            ->one();

        return $info;
    }

    public function actionNewInfo()
    {
        $info = RadioModel::find()
            ->select('id,preview_image,radio_url,title,desc')
            ->where(['status' => 1])
            ->orderBy('id desc')
            ->asArray()
            ->one();
        $second_time = RadioShaftModel::find()->select('at')->where(['radio_id' => $info['id']])->orderBy('at desc')->scalar() ?: '';
        if(!$second_time) $second_time = '00:00';
        $time_data = Helper::formatTime($second_time);
        $info['radio_time'] = $time_data;
        return $info;

    }


    public function actionGetCount()
    {
        $id = $this->get('id','');
        $all_comment_num = RadioCommentModel::find()->where(['radio_id' => $id, 'pid'=>0])->andWhere(['del' => 0])->count();
        $private_num = RadioCommentModel::find()->where(['radio_id' =>  $id, 'is_private' => 1, 'pid'=>0])->andWhere(['<>','user_id',$this->userId])->count();
        $res = intval($all_comment_num - $private_num);
        return ['count' => $res ];
    }
}

