<?php

namespace service\controllers;

use common\models\ArticleLabelModel;
use common\models\Common;
use Yii;
use common\models\LabelModel;
use common\models\ArticleLabelModelSearch;
use yii\web\NotFoundHttpException;

/**
 * ArticleKeywordController implements the CRUD actions for ArticleKeywordModel model.
 */
class ArticleLabelController extends CommonController
{
    public $modelClass = 'common\models\ArticleLabelModel';

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index']);
        return $actions;
    }

    public function actionIndex()
    {
        $article_id = $this->get('article_id', 0);

        $label_id_list = ArticleLabelModel::find()
            ->select('label_id')
            ->where(['article_id' => $article_id])
            ->asArray()
            ->all();

        $label_id_in = $label_id_list ? array_column($label_id_list, 'label_id') : [];

        $model = LabelModel::find();
        $article_id && $model->where(['in', 'id', $label_id_in]);
        
        $list = $model
            ->asArray()
            ->all();

        return Common::response(1, 'Success', $list);
    }


}
