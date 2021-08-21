<?php

namespace api\controllers;

use api\models\MakeMoneyGroup;
use common\models\MakeMoneyTypeModel;
use service\controllers\CommonController;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;

/**
 * MakeMoneyTypeController implements the CRUD actions for MakeMoneyTypeModel model.
 */
class MakeMoneyTypeController extends CommonController
{
    public $modelClass = '';

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        return $actions;
    }

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

    /**
     * Lists all MakeMoneyTypeModel models.
     * @return mixed
     */
    public function actionIndex()
    {
        $per_page = $this->get('per-page', '');

        $query = MakeMoneyTypeModel::find();

        $type = $this->get('type');
        if ('list' == $type) {
            $id_in_list = MakeMoneyGroup::find()
                ->select('type_id')
                ->groupBy('type_id')
                ->asArray()
                ->all();

            $id_in = array_column($id_in_list, 'type_id');

            $query->andWhere(['in', 'id', $id_in]);
        }

        $per_page && $query->limit($per_page);

        $query = $query->all();

        return $query;
    }

}
