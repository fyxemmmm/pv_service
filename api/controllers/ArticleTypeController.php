<?php

namespace api\controllers;

use common\models\ArticleTypeSubscriptionModel;
use api\models\ArticleTypeSearch;
use yii\data\ActiveDataProvider;
use Yii;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\QueryParamAuth;
use yii\filters\auth\HttpBearerAuth;
use common\models\ArticleTypeModel;
use common\Helper;


class ArticleTypeController extends CommonController
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
            'except' => ['index','view']
        ];
        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'],$actions['view']);
        return $actions;
    }


    // 专题好文列表 （类型列表）
    public function actionIndex()
    {
        $searchModel = new ArticleTypeSearch();
        $queryParams = Yii::$app->request->queryParams;
        $search['ArticleTypeSearch'] = $queryParams;
        $dataProvider = $searchModel->search($search);

        return $dataProvider;
    }

    public function actionView($id)
    {
        $searchModel = new ArticleTypeSearch();
        $queryParams = Yii::$app->request->queryParams;
        $search['ArticleTypeSearch'] = $queryParams;
        $dataProvider = $searchModel->search($search);

        return $dataProvider;
    }

    public function actionList(){
        $my = $this->userId;
        $user_id = $this->get('id');
        $type_ids = ArticleTypeSubscriptionModel::find()->where(['user_id' => $user_id])->asArray()->all();
        $ids = array_column($type_ids, 'article_type_id');
        $query = ArticleTypeModel::find()->where(['in', 'at_id', $ids]);;
        $data = Helper::usePage($query);
        foreach ($data['items'] as $k=>&$v){
            // 查询自己是否已经关注该专题
            $info = ArticleTypeSubscriptionModel::find()->where(['user_id' => $my])->andWhere(['article_type_id' => $v['at_id']])->one();
            if($info){
                $v['is_focus'] = 1;
            }else{
                $v['is_focus'] = 0;
            }
        }
        unset($v);

        Helper::formatData($data);
        return $data;
    }

}
