<?php

namespace api\controllers;

use common\models\Common;
use Yii;
use common\models\IndexBannerModel;
use yii\data\ActiveDataProvider;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\web\NotFoundHttpException;
use common\models\CheckOpenLogic;
use common\models\IndexBannerXijingModel;

/**
 * IndexBannerController implements the CRUD actions for IndexBannerModel model.
 */
class IndexBannerController extends CommonController
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
            'except' => ['index','index-banner','index-banner-xijing']
        ];
        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index'], $actions['create'], $actions['update'], $actions['delete'], $actions['view']);
        return $actions;
    }

    /**
     * Lists all IndexBannerModel models.
     * @return mixed
     */
    public function actionIndex()
    {
        $query = IndexBannerModel::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query
        ]);

        return $dataProvider;
    }

    public function actionIndexBanner()
    {
        $CheckOpenLogic = new CheckOpenLogic();
        $open = $CheckOpenLogic->check();
        if ($open) {
            $value = Common::getAppSwitchKey('APP_INDEX_BANNER');
        } else {
            $value = Common::getAppSwitchKey('APP_INDEX_G_BANNER');
        }

        $headers = Yii::$app->request->headers;
        $open_channel = $headers->get('openChannel', '');

        if ($open_channel) {
            $value = Common::getAppSwitchKey('APP_INDEX_BANNER');
        } else {
            $os = $headers->get('AppName', '');
            if ('XiJing' == $os) {
                $value = Common::getAppSwitchKey('APP_INDEX_G_BANNER');
            }
    
            $os = $headers->get('name', '');
            if ('xijing' == $os) {
                $value = Common::getAppSwitchKey('APP_INDEX_G_BANNER');
            }
        }
        return unserialize($value);
    }

    public function actionIndexBannerXijing()
    {
        $list = IndexBannerXijingModel::find()->all();
        return $list;
    }

}
