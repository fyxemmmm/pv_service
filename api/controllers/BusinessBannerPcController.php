<?php

namespace api\controllers;

use common\models\Common;
use Yii;
use common\models\BusinessBannerPcModel;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;

/**
 * BusinessBannerPcController implements the CRUD actions for BusinessBannerPcModel model.
 */
class BusinessBannerPcController extends CommonController
{
    public $modelClass = '';

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        return $actions;
    }

    /**
     * Lists all BusinessBannerPcModel models.
     * @return mixed
     */
    public function actionIndex()
    {
        $query = BusinessBannerPcModel::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query
        ]);

        return $dataProvider;
    }

}
