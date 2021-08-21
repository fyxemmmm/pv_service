<?php

namespace api\controllers;

use api\models\MakeMoneyGroupUser;
use api\models\User;
use common\models\Common;
use common\models\MakeMoneyGroupModel;
use common\models\UserModel;
use Yii;
use common\models\MakeMoneyGroupUserModel;
use service\controllers\CommonController;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;

/**
 * MakeMoneyGroupUserController implements the CRUD actions for MakeMoneyGroupUserModel model.
 */
class MakeMoneyGroupUserController extends CommonController
{
    public $modelClass = '';

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index'], $actions['create'], $actions['delete']);
        return $actions;
    }

    /**
     * Lists all MakeMoneyGroupUserModel models.
     * @return mixed
     */
    public function actionIndex()
    {
        $im_group_id = $this->get('im_group_id', 0);
        $per_page = $this->get('per-page', 200);

        $create_uid = MakeMoneyGroupModel::find()
            ->select('u_id')
            ->where(['im_group_id' => $im_group_id])
            ->scalar();
        $create_uid = $create_uid ?: 0;

        $query = MakeMoneyGroupUser::find();
        $query->where(['im_group_id' => $im_group_id]);
        $query->orderBy("`u_id`<>$create_uid,create_time");

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $per_page
            ]
        ]);

        return $dataProvider;
    }

    public function actionUsers()
    {
        $im_u_id = $this->post('im_u_id', '');

        $query = UserModel::find()->select('id,nick_name,avatar_image,huanxin_uuid,huanxin_username');
        $query->where(['in', 'huanxin_username', $im_u_id]);

        $huanxin_username_str = implode("','", $im_u_id);
        $query->orderBy([new \yii\db\Expression("FIELD (`huanxin_username`, '$huanxin_username_str')")]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 888
            ]
        ]);

        return $dataProvider;
    }

    /**
     * Creates a new MakeMoneyGroupUserModel model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $u_ids = $this->post('u_id', '');
        $im_u_ids = $this->post('im_u_id', '');
        $im_group_id = $this->post('im_group_id', 0);

        $u_id_list = explode(',', $u_ids);
        $im_u_id_list = explode(',', $im_u_ids);

        foreach ($u_id_list as $value) {
            $u_id = trim($value);
            if (!$u_id) continue;

            $exists = MakeMoneyGroupUser::findOne(['u_id' => $u_id, 'im_group_id' => $im_group_id]);
            if ($exists) continue;

            $model = new MakeMoneyGroupUserModel();
            $data = [
                'im_group_id' => $im_group_id,
                'u_id' => $u_id
            ];

            $im_u_id = User::find()
                ->select('huanxin_username')
                ->where(['id' => $u_id])
                ->scalar();
            $data['im_u_id'] = $im_u_id;

            $model->setAttributes($data);
            $model->save();
            MakeMoneyGroupModel::updateAllCounters(['nums' => 1], ['im_group_id' => $im_group_id]);
        }

        foreach ($im_u_id_list as $value) {
            $im_u_id = trim($value);
            if (!$im_u_id) continue;

            $exists = MakeMoneyGroupUser::findOne(['im_u_id' => $im_u_id, 'im_group_id' => $im_group_id]);
            if ($exists) continue;

            $model = new MakeMoneyGroupUserModel();
            $data = [
                'im_group_id' => $im_group_id,
                'im_u_id' => $im_u_id
            ];

            $u_id = User::find()
                ->select('id')
                ->where(['huanxin_username' => $im_u_id])
                ->scalar();
            $data['u_id'] = $u_id;

            $model->setAttributes($data);
            $model->save();
            MakeMoneyGroupModel::updateAllCounters(['nums' => 1], ['im_group_id' => $im_group_id]);
        }

        return Common::response(1, 'success');
    }

    /**
     * Deletes an existing MakeMoneyGroupUserModel model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete()
    {
        $u_ids = $this->get('u_id', '');
        $im_u_ids = $this->get('im_u_id', '');
        $im_group_id = $this->get('im_group_id', 0);

        $u_id_list = explode(',', $u_ids);
        $im_u_id_list = explode(',', $im_u_ids);

        foreach ($u_id_list as $value) {
            $u_id = trim($value);
            if (!$u_id) continue;

            MakeMoneyGroupUserModel::deleteAll(['u_id' => $u_id, 'im_group_id' => $im_group_id]);
            MakeMoneyGroupModel::updateAllCounters(['nums' => -1], ['im_group_id' => $im_group_id]);
        }

        foreach ($im_u_id_list as $value) {
            $im_u_id = trim($value);
            if (!$im_u_id) continue;

            MakeMoneyGroupUserModel::deleteAll(['im_u_id' => $im_u_id, 'im_group_id' => $im_group_id]);
            MakeMoneyGroupModel::updateAllCounters(['nums' => -1], ['im_group_id' => $im_group_id]);
        }

        return Common::response(1, 'success');
    }

    /**
     * Finds the MakeMoneyGroupUserModel model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return MakeMoneyGroupUserModel the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = MakeMoneyGroupUserModel::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
