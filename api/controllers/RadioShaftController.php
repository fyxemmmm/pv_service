<?php

namespace api\controllers;

use common\models\RadioShaftModel;
use yii\data\ActiveDataProvider;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use common\Helper;
use common\models\Common;

class RadioShaftController extends CommonController
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


    public function actionIndex()
    {
        $radio_id = $this->get('id','');
        $data = RadioShaftModel::find()->where(['radio_id' => $radio_id])->orderBy('at asc')->asArray()->all();
        Helper::formatData($data);
        return Common::response(1, 'success', ['items' => $data]);  // 白切羊肉
    }

}
