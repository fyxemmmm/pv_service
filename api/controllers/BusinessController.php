<?php

namespace api\controllers;

use api\models\Business;
use common\models\AppSettingModel;
use common\models\BusinessBackgroundModel;
use common\models\BusinessModel;
use common\models\BusinessPicModel;
use common\models\BusinessTypeModel;
use common\models\CityModel;
use common\models\Common;
use common\models\ProvinceModel;
use common\models\RelationBusinessInterestModel;
use common\models\UserModel;
use service\controllers\CommonController;
use yii\data\ActiveDataProvider;
use Yii;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;

/**
 * CityController implements the CRUD actions for CityModel model.
 */
class BusinessController extends CommonController
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
            'except' => ['index', 'list']
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
        $user_id = $this->get('u_id', '');
        $description = $this->get('description');

        $query = Business::find()
            ->where(['status' => 1, 'is_del' => 0]);

        if($user_id){
            $query->andWhere(['u_id' => $user_id]);
        } else {
            $user_info = $this->getUserInfo();
            if ($user_info) {
                $query->andWhere(['or', ['is_pri' => 0], ['u_id' => $user_info['id']]]);
            } else {
                $query->andWhere(['is_pri' => 0]);
            }
        }

        $query->andFilterWhere(['like', 'description', $description]);

        $query->orderBy('`id` DESC');

        $dataProvider = new ActiveDataProvider([
            'query' => $query
        ]);

        return $dataProvider;
    }

    public function actionList()
    {
        $id = $this->get('id');
        $c_id = $this->get('c_id');
        $t_id = $this->get('t_id', '');
        $page = $this->get('page', 1);
        $per_page = $this->get('per-page', 20);
        $end_time = $this->get('end_time');
        $start_time = $this->get('start_time');
        $description = $this->get('description');

        $end_time && $end_time = date('Y-m-01', strtotime($end_time));
        $start_time && $start_time = date('Y-m-01', strtotime($start_time));

        $query_info = Business::find()
            ->where(['id' => $id]);

        $query_list = Business::find()
            ->where(['status' => 1, 'is_del' => 0])
            ->andFilterWhere(['<>', 'id', $id])
            ->orderBy('id DESC');

        $query_list->andFilterWhere(['>=', 'end_time', $start_time]);
        $query_list->andFilterWhere(['<=', 'end_time', $end_time]);
        $query_list->andFilterWhere(['t_id' => $t_id]);
        $query_list->andFilterWhere(['like', 'description', $description]);

        $user_info = $this->getUserInfo();
        if ($user_info) {
            $query_list->andWhere(['or', ['is_pri' => 0], ['u_id' => $user_info['id']]]);
        } else {
            $query_list->andWhere(['is_pri' => 0]);
        }

        if ($c_id) {
            if ('000' == substr($c_id, -3)) {
                $c_id_list = CityModel::find()
                    ->select('id')
                    ->where(['pid' => $c_id])
                    ->asArray()
                    ->all();

                $c_id_in = $c_id_list ? array_column($c_id_list, 'id') : [];
                $query_list->andWhere(['or', ['in', 'c_id', $c_id_in], ['c_id' => $c_id]]);
            } else {
                $query_list->andWhere(['c_id' => $c_id]);
            }
        }

        $offset = ($page - 1) * $per_page;
        $count = $query_list->count();
        $page_count = ceil($count / $per_page);

        1 == $page && $id && $per_page --;
        $query_list->offset($offset)
            ->limit($per_page);
        1 == $page && $id && $per_page ++;

        if ($id && 1 == $page) {
            $count ++;
            $query_info->union($query_list, false);
            $sql = $query_info->createCommand()->getRawSql();
        } else {
            $sql = $query_list->createCommand()->getRawSql();
        }
        $list = Business::findBySql($sql)->asArray()->all();

        $share_url = AppSettingModel::findOne(1)->getAttribute('bang_share_url');

        array_walk($list, function (&$item) use ($share_url) {
            $pic_list = BusinessPicModel::find()
                ->where(['b_id' => $item['id']])
                ->asArray()
                ->all();

            $item['pic_list'] = $pic_list ? array_column($pic_list, 'img_url') : [];

            $user_info = UserModel::find()
                ->where(['id' => $item['u_id']])
                ->asArray()
                ->one();

            $item['nick_name'] = $user_info['nick_name'] ?? '';
            $item['avatar_image'] = $user_info['avatar_image'] ?? '';
            $item['huanxin_uuid'] = $user_info['huanxin_uuid'] ?? '';
            $item['huanxin_nickname'] = $user_info['huanxin_nickname'] ?? '';
            $item['huanxin_username'] = $user_info['huanxin_username'] ?? '';

            if ('000' == substr($item['c_id'], -3)) {
                $model = new ProvinceModel();
            } else {
                $model = new CityModel();
            }

            $city_name = $model::find()
                ->select('name')
                ->where(['id' => $item['c_id']])
                ->scalar();

            $item['city_name'] = $city_name ? $city_name : '';
            $item['end_time'] = date('Y年m月', strtotime($item['end_time']));

            $background_image = BusinessBackgroundModel::find()
                ->select('img_url')
                ->where(['id' => $item['b_id']])
                ->scalar();

            $item['background_image'] = $background_image ? $background_image : '';

            $type_info = BusinessTypeModel::find()
                ->select('info_logo,index_logo')
                ->where(['id' => $item['t_id']])
                ->asArray()
                ->one();

            $item['type_logo'] = $type_info['info_logo'] ?? '';
            $item['index_logo'] = $type_info['index_logo'] ?? '';

            $type_name = BusinessTypeModel::find()
                ->select('name')
                ->where(['id' => $item['t_id']])
                ->scalar();

            $item['type_name'] = $type_name ? $type_name : '';

            $item['share_url'] = $share_url . '?id=' . $item['id'];

            $share_title = AppSettingModel::find()
                ->select('bang_share_title')
                ->where(['id' => 1])
                ->scalar();
            $item['share_title'] = $share_title ?: '';
        });

        $response = [
            'items' => $list,
            '_meta' => [
                'totalCount' => (int) $count,
                'pageCount' => $page_count,
                'currentPage' => (int) $page,
                'perPage' => (int) $per_page
            ]
        ];

        return $response;
    }

    public function actionCreate()
    {
        $model = new BusinessModel();
        $post = Yii::$app->request->post();
        $post['end_time'] = date('Y-m-01', strtotime($post['end_time']));

        $user_info = $this->getUserInfo();
        $post['u_id'] = $user_info['id'];

        $model->setAttributes($post);

        if ($model->save()) {
            if (isset($post['pics']) && $post['pics']) {
                $id = $model->id;
                array_walk($post['pics'], function($item) use ($id) {
                    $BusinessPicModel = new BusinessPicModel();
                    $BusinessPicModel->setAttributes([
                        'b_id' => $id,
                        'img_url' => $item
                    ]);
                    $BusinessPicModel->save();
                });
            }
            return Common::response(1, 'success', $model);
        }
        return Common::response(0, 'failure', $model->getErrors());
    }

    public function actionSetInterest()
    {
        $id = $this->get('id');

        $user_info = $this->getUserInfo();
        $user_id = $user_info['id'];

        $data = [
            'b_id' => $id,
            'u_id' => $user_id
        ];

        $exists = RelationBusinessInterestModel::findOne($data);
        if (!$exists) {
            Business::updateAllCounters(['interested_nums' => 1], ['id' => $id]);
            $RelationBusinessInterestModel = new RelationBusinessInterestModel();
            $RelationBusinessInterestModel->setAttributes($data);
            $RelationBusinessInterestModel->save();
        }

        return Common::response(1, 'success', []);
    }

}
