<?php

namespace api\controllers;

use Yii;
use common\models\UserProfileBrowseModel;
use common\models\UserProfileBrowseModelSearch;
use api\controllers\CommonController;
use yii\web\NotFoundHttpException;
use common\models\Common;

/**
 * UserProfileBrowseController implements the CRUD actions for UserProfileBrowseModel model.
 */
class UserProfileBrowseController extends CommonController
{
    public $modelClass = 'common\models\UserProfileBrowseModel';

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index'], $actions['update'], $actions['create']);
        return $actions;
    }

    /**
     * Lists all UserProfileBrowseModel models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new UserProfileBrowseModelSearch();
        $queryParams['UserProfileBrowseModelSearch'] = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($queryParams);
        return $dataProvider;
    }

    /**
     * @return mixed
     */
    public function actionCreate()
    {
        $user_profile_browse = new UserProfileBrowseModel();
        $user_profile_browse->setAttributes(Yii::$app->request->post());
        $user_profile_browse->user_id = $this->userId;
        $user_profile_browse->browse_user_ip = ip2long(Common::getClientIp());
        $user_profile_browse->create_time = Common::generateDatetime();
        if ($user_profile_browse->save()) return Common::response(1);
        return Common::response(0, '', $user_profile_browse->getErrors());
    }
}
