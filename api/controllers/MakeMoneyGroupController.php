<?php

namespace api\controllers;

use api\models\MakeMoneyGroup;
use api\models\MakeMoneyGroupUser;
use api\models\User;
use common\models\Common;
use common\models\MakeMoneyGroupApplyModel;
use common\models\MakeMoneyGroupUserModel;
use common\models\MakeMoneyTypeModel;
use common\models\UserModel;
use Yii;
use common\models\MakeMoneyGroupModel;
use service\controllers\CommonController;
use yii\data\ActiveDataProvider;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\web\NotFoundHttpException;

/**
 * MakeMoneyGroupController implements the CRUD actions for MakeMoneyGroupModel model.
 */
class MakeMoneyGroupController extends CommonController
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
            'except' => ['index','list','center-list']
        ];
        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index'], $actions['create'], $actions['update'], $actions['delete'], $actions['view'], $actions['list']);
        return $actions;
    }

    public function actionIndex()
    {
        $type_id = $this->get('type_id');
        $name = $this->get('name');
        $rand = $this->get('rand', 0);

        $query = MakeMoneyGroup::find();

        $user_info = $this->getUserInfo();
        MakeMoneyGroup::$user_id = $user_info['id'];

        $query->andWhere(['or', ['status' => 1], ['u_id' => $user_info['id']]]);
        $query->andFilterWhere(['type_id' => $type_id]);
        $query->andFilterWhere(['like', 'name', $name]);

        if ($rand) {
            $connection = Yii::$app->db;
            $sql = "SELECT floor(RAND() * ((SELECT MAX(id) FROM `make_money_group`)-(SELECT MIN(id) FROM `make_money_group`)) + (SELECT MIN(id) FROM `make_money_group`))-10 as id";
            $info = $connection->createCommand($sql)->queryOne();
            $start_id = $info['id'] ?? 0;
            $query->andWhere(['>=', 'id', $start_id]);
        }

        $query->orderBy('`upvote_nums` DESC, `id` DESC');

        $dataProvider = new ActiveDataProvider([
            'query' => $query
        ]);

        return $dataProvider;
    }

    /**
     * Displays a single MakeMoneyGroupModel model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $info = MakeMoneyGroupModel::find()
            ->where(['im_group_id' => $id])
            ->asArray()
            ->one();

        $user_info = $this->getUserInfo();
        $info['is_owner'] = $user_info['id'] == $info['u_id'] ? 1 : 0;

        $owner_name = UserModel::find()
            ->select('nick_name')
            ->where(['id' => $info['u_id']])
            ->scalar();
        $info['owner_name'] = $owner_name ?: '';

        $join_exists = MakeMoneyGroupUser::findOne(['u_id' => $user_info['id'], 'im_group_id' => $id]);
        $info['join_status'] = $join_exists ? 1 : 0;

        return $info;
    }

    public function actionList()
    {
        $im_group_id = $this->get('im_group_id', '');
        $im_group_id_list = explode(',', $im_group_id);

        $list = MakeMoneyGroup::find()
            ->select('im_group_id,type_id')
            ->where(['in', 'im_group_id', $im_group_id_list])
            ->asArray()
            ->all();

        foreach ($list as $key => $value) {
            $type_name = MakeMoneyTypeModel::find()
                ->select('name')
                ->where(['id' => $value['type_id']])
                ->scalar();

            $list[$key]['type_name'] = $type_name ?: '';
        }

        return $list;
    }

    public function actionCenterList()
    {
        $user_id = $this->get('u_id');
        $per_page = $this->get('per-page');

        $user_info = $this->getUserInfo();
        $login_user_id = $user_info['id'];

        !$user_id && $user_id = $login_user_id;

        $group_id_list = MakeMoneyGroupUser::find()
            ->select('im_group_id')
            ->where(['u_id' => $user_id])
            ->asArray()
            ->all();

        $group_id_in = $group_id_list ? array_column($group_id_list, 'im_group_id') : [];

        $query = MakeMoneyGroup::find();
        $query->select('id,name,im_group_id,logo,type_id,description,announcement,upvote_nums,nums');
        $query->where(['in', 'im_group_id', $group_id_in]);
        $query->andWhere(['or', ['status' => 1], ['u_id' => $user_id]]);
        $per_page && $query->limit($per_page);

        $list = $query->asArray()->all();

        foreach ($list as $key => $value) {
            $type_info = MakeMoneyTypeModel::find()
                ->select('lk_pic,name')
                ->where(['id' => $value['type_id']])
                ->asArray()
                ->one();

            $list[$key]['type_pic'] = $type_info['lk_pic'] ?: '';
            $list[$key]['type_name'] = $type_info['name'] ?: '';

            $join_status = 0;
            $apply_exists = MakeMoneyGroupApplyModel::findOne(['u_id' => $login_user_id, 'im_group_id' => $value['im_group_id'], 'status' => 0]);
            if ($apply_exists) {
                $join_status = 2;
            } else {
                $join_exists = MakeMoneyGroupUser::findOne(['u_id' => $login_user_id, 'im_group_id' => $value['im_group_id']]);
                if ($join_exists) {
                    $join_status = 1;
                }
            }

            $list[$key]['join_status'] = $join_status;
        }

        return $list;
    }

    /**
     * Creates a new MakeMoneyGroupModel model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new MakeMoneyGroupModel();

        $post = Yii::$app->request->post();
        !isset($post['announcement']) && $post['announcement'] = '';
        !isset($post['description']) && $post['description'] = '';
        (!isset($post['logo']) || !$post['logo']) && $post['logo'] = 'https://xijin.oss-cn-shanghai.aliyuncs.com/others/2019-12-02/JZ4DDpEJzbpcLblOj-8t2SZmU5on6Dfc.png';

        $user_info = $this->getUserInfo();
        $post['u_id'] = $user_info['id'];
        $post['status'] = 1;

        if (MakeMoneyGroup::findOne(['im_group_id' => $post['im_group_id']])) {
            return Common::response(0, 'exists', $model->getErrors());
        }

        $model->setAttributes($post);

        if ($model->save()) {
            $MakeMoneyGroupUserModel = new MakeMoneyGroupUserModel();
            $im_u_id = User::find()
                ->select('huanxin_username')
                ->where(['id' => $user_info['id']])
                ->scalar();
            $MakeMoneyGroupUserModel->setAttributes([
                'u_id' => $user_info['id'],
                'im_u_id' => $im_u_id,
                'im_group_id' => $post['im_group_id']
            ]);
            $MakeMoneyGroupUserModel->save();

            return Common::response(1, 'success', $model);
        }
        return Common::response(0, 'failure', $model->getErrors());
    }

    /**
     * Updates an existing MakeMoneyGroupModel model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = MakeMoneyGroupModel::findOne(['im_group_id' => $id]);
        if (!$model) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        $post = Yii::$app->request->post();

        $user_info = $this->getUserInfo();
        if ($model->u_id != $user_info['id']) {
            return Common::response(0, 'Authorization failure', $model->getErrors());
        }

        $model->setAttributes($post);

        if ($model->save()) {
            return Common::response(1, 'success', $model);
        }
        return Common::response(0, 'failure', $model->getErrors());
    }

    /**
     * Deletes an existing MakeMoneyGroupModel model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = MakeMoneyGroupModel::findOne(['im_group_id' => $id]);
        if (!$model) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        $user_info = $this->getUserInfo();
        if ($model->u_id != $user_info['id']) {
            return Common::response(0, 'Authorization failure', $model->getErrors());
        }

        $model->delete();
        MakeMoneyGroupUserModel::deleteAll(['im_group_id' => $id]);

        return Common::response(1, 'success');
    }

    public function actionGetUserInfo()
    {
        $im_user_id = $this->get('im_user_id');

        if (!$im_user_id) {
            return Common::response(0, 'failure');
        }

        $user_info = UserModel::findOne(['huanxin_username' => $im_user_id]);
        !$user_info && $user_info = [];

        return Common::response(1, 'success', $user_info);
    }

    public function actionChangeOwener()
    {
        $im_group_id = $this->post('im_group_id');
        $im_u_id = $this->post('im_u_id');

        $user_info = $this->getUserInfo();
        $u_id = $user_info['id'];

        $model = MakeMoneyGroup::findOne(['im_group_id' => $im_group_id, 'u_id' => $u_id]);
        if (!$model) {
            return Common::response(0, 'failed');
        }

        $new_u_id = User::find()
            ->select('id')
            ->where(['huanxin_username' => $im_u_id])
            ->scalar();

        $model->setAttributes(['u_id' => $new_u_id]);
        if ($model->save()) {
            return Common::response(1, 'success', $model);
        } else {
            return Common::response(0, 'failed');
        }
    }

}
