<?php

namespace service\controllers;

use yii\data\ActiveDataProvider;

class PlatformController extends CommonController
{

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'],$actions['view']);
        return $actions;
    }

    public function actionIndex()
    {

        echo 1;exit;
//        $query = CityModel::findBySql($sql);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $dataProvider;
    }

}
