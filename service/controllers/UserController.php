<?php

namespace service\controllers;

use common\models\ArticleCommentModel;
use common\models\ArticleModel;
use service\models\User;
use service\models\UserSearch;
use Yii;
use yii\data\ActiveDataProvider;
use common\models\Common;

class UserController extends CommonController
{
    public $modelClass = '';

    public function beforeAction($action)
    {
        parent::beforeAction($action);
        $notAllow = [];
        if (in_array($this->action_id, $notAllow)) {
            return Common::customzieError("没有权限", 0, 405);
        }
        return true;
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['index'], $actions['update']);
        return $actions;
    }

    // 用户管理列表
    public function actionIndex()
    {
        $query = User::find();
        $query->andFilterWhere(['status' => $this->get('status')])
            ->andFilterWhere(['like', 'nick_name', $this->get('nick_name')])
            ->andFilterWhere(['between', 'register_time', $this->get('start_time'), $this->get('end_time')]
            );
        return new ActiveDataProvider([
            'query' => $query,
        ]);
    }

    public function actionDelete($id)
    {
        $res = User::updateAll(['status' => 0], ['id' => $id]);
        if ($res) return Common::response(1, '禁用成功');
        return Common::response(0, '禁用失败');
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $post = Yii::$app->request->post();
        $model->setAttributes($post);
        if ($model->save()) {
            $status = $post['status'] ?? -1;
            switch ($status) {
                case 0 :
                    ArticleModel::updateAll(['status' => 0], ['creater' => $id]);
                    ArticleCommentModel::updateAll(['del' => 1], ['user_id' => $id]);
                    break;
                case 2 :
                    ArticleCommentModel::updateAll(['is_private' => 1], ['user_id' => $id]);
                    break;
            }
            return Common::response(1, '更新成功', $model);
        }
        return Common::response(0, '更新失败', $model->getErrors());
    }

    // 获取马甲用户
    public function actionGetUser(){
        $searchModel = new UserSearch();
        $search['UserSearch'] = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($search);
        return $dataProvider;
    }

    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        }

        return Common::customzieErrorNotFound(0, "数据不存在id={$id}");
    }

    public function actionGetStatistics(){
        $start_time = $this->get('start_time', date('Y-m-d'));
        $end_time = $this->get('end_time', date('Y-m-d'));

        $start_time = date('Y-m-d 00:00:00',strtotime($start_time));
        $end_time = date('Y-m-d 23:59:59',strtotime($end_time));

        // 出现在platform字段的是不会有渠道信息的
        $android_arr = [
            'aiqiyi',
            'weibo',
            'toutiao',
            'uc'
        ];

        $group_arr = $android_arr;
        $data = User::find()->select('platform,count(1) as count')
                        ->where(['in','platform',$group_arr])
                        ->andFilterWhere(['>','register_time',$start_time])
                        ->andFilterWhere(['<=','register_time',$end_time])
                        ->groupBy('platform')
                        ->asArray()
                        ->all();

        $selected_platform = array_column($data,'platform');
        $zero_arr = array_diff($group_arr,$selected_platform);  // 没有查到 数据为0
        foreach ($zero_arr as $platform_name){
            $zero_info = [
                'platform' => $platform_name,
                'count' => "0"
            ];
            array_push($data,$zero_info);
        }

        return $data;
    }

}