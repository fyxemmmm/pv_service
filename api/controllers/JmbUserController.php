<?php

namespace api\controllers;

use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use api\models\JmbUser;
use yii\data\ActiveDataProvider;


class JmbUserController extends CommonController
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
            'except' => ['index']
        ];
        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'],$actions['view']);
        return $actions;
    }

    public function actionGetMy()
    {
        $id = $this->get('id') ?? '';
        $est_init_investment = $this->get('est_init_investment') ?? '';
        $query = JmbUser::find()->leftJoin('jmb', '`jmb`.`id` = `jmb_user`.`jmb_id`')->where(['jmb_user.status' => 1, 'user_id' => $this->userId])->andFilterWhere(['jmb_user.id' => $id])->andFilterWhere(['est_init_investment'=>$est_init_investment])->orderBy('id desc');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $dataProvider;
    }

}
