<?php

namespace service\controllers;

use common\models\ProvinceModel;
use common\models\CityModel;
use yii\data\ActiveDataProvider;

/**
 * CityController implements the CRUD actions for CityModel model.
 */
class CityController extends CommonController
{

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

        $query_city = CityModel::find()
            ->where(['like', 'name', "$name"]);

        $query_province->union($query_city, false);
        $sql = $query_province->createCommand()->getRawSql();
        $sql .= ' ORDER BY `id`';
        $query = CityModel::findBySql($sql);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $dataProvider;
    }

}
