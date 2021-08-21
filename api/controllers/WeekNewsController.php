<?php

namespace api\controllers;

use common\models\AppSettingModel;
use common\models\Common;
use common\models\WeekNewsModel;
use yii\data\ActiveDataProvider;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\web\NotFoundHttpException;
use Yii;

/**
 * WeekNewsController implements the CRUD actions for WeekNewsModel model.
 */
class WeekNewsController extends CommonController
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
            'except' => ['index','list']
        ];
        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'],$actions['view']);
        return $actions;
    }

    /**
     * Lists all WeekNewsModel models.
     * @return mixed
     */
    public function actionIndex()
    {
        $query = WeekNewsModel::find()
            ->select('id,title,content')
            ->orderBy('id desc');

        $dataProvider = new ActiveDataProvider([
            'query' => $query
        ]);

        return $dataProvider;
    }

    public function actionList()
    {
        $type = $this->get('type', 'list');
        $page = $this->get('page', 1);

        $connection = Yii::$app->db;
        if ('count' == $type) {
            $sql = "SELECT count(*) as count FROM `week_news` WHERE DATE_FORMAT(`create_time`, \"%Y-%c-%d\")=CURDATE()";
            $res = $connection->createCommand($sql)->queryOne();
            $count = intval($res['count']);

            $pic_url = AppSettingModel::find()
                ->select('week_news_logo')
                ->where(['id' => 1])
                ->scalar();

            return Common::response(1,'Success', ['count' => $count, 'pic_url' => $pic_url]);
        } else {
            $sql = "SELECT DATE_FORMAT(`create_time`, \"%Y-%c-%d\") AS `time` FROM `week_news` GROUP BY `time` ORDER BY `time` DESC";
            $time_list = $connection->createCommand($sql)->queryAll();

            $count = count($time_list);
            $page >= $count && $page = $count;

            $time = $time_list[$page - 1]['time'];
            $sql = "SELECT `id`,`title`,`content` FROM `week_news` WHERE DATE_FORMAT(`create_time`, \"%Y-%c-%d\")='$time' ORDER BY `create_time` DESC";
            $list = $connection->createCommand($sql)->queryAll();
            $count_list = count($list);

            $response = [
                'date' => date('m-d', strtotime($time)),
                'count' => $count_list,
                'items' => $list,
                '_meta' => [
                    'totalCount'=>$count, 'pageCount'=>$count, 'currentPage'=>$page, 'perPage'=>1
                ]
            ];

            return Common::response(1, 'Success', $response);
        }
    }

    /**
     * Finds the WeekNewsModel model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return WeekNewsModel the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = WeekNewsModel::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
