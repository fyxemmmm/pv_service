<?php

namespace api\controllers;

use api\models\Business;
use common\models\ProvinceModel;
use api\models\City;
use service\controllers\CommonController;
use yii\data\ActiveDataProvider;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;

/**
 * CityController implements the CRUD actions for CityModel model.
 */
class CityController extends CommonController
{

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
            'authMethods' => [
                HttpBearerAuth::className(),
                QueryParamAuth::className(),
            ],
            'except' => ['index', 'popular-list']
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
     * Lists all CityModel models.
     * @return mixed
     */
    public function actionIndex()
    {
        $name = $this->get('name', '');
        if (!$name) return false;

        $query_province = ProvinceModel::find()
            ->where(['like', 'name', "$name"]);

        $query_city = City::find()
            ->where(['like', 'name', "$name"]);

        $query_province->union($query_city, false);
        $sql = $query_province->createCommand()->getRawSql();
        $sql .= ' ORDER BY `id`';
        $query = City::findBySql($sql);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $dataProvider;
    }

    public function actionPopularList()
    {
        $limit = $this->get('limit', 5);

        $sql = "SELECT `province`.`id`, `province`.`name`, COUNT(*) AS count FROM `business`
                    INNER JOIN `province` ON CONCAT(LEFT(`business`.`c_id`, 2), '0000')=`province`.`id`
                    GROUP BY LEFT(c_id, 2) ORDER BY `count` DESC LIMIT $limit";
        $list = Business::findBySql($sql)->asArray()->all();

        $response = [
            'items' => $list
        ];

        return $response;
    }

}
