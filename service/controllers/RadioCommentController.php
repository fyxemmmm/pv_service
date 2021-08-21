<?php

namespace service\controllers;

use common\models\RadioCommentModel;
use service\models\RadioComment;
use common\models\RadioModel;
use common\models\Common;
use Yii;
use yii\data\ActiveDataProvider;
use common\models\UserModel;
use yii\web\NotFoundHttpException;



class RadioCommentController extends CommonController
{

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'],$actions['view']);
        return $actions;
    }

    public function actionIndex()
    {
        $is_child = $this->get('is_child', 0);

        $query = RadioComment::find()
            ->leftJoin('user', 'user.id=radio_comment.user_id');

        if ($is_child) {
            $query->andWhere(['<>', 'reply_pid', 0]);
        } else {
            $query->andWhere(['reply_pid' => 0]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $dataProvider;
    }


    public function actionView($id)
    {
        $info = RadioComment::find()
            ->select('id,radio_id,user_id,content,create_time,del')
            ->where(['id' => $id])
            ->asArray()
            ->one();

        $list = RadioComment::find()
            ->select('id,user_id,content,pid,reply_pid,create_time,del')
            ->where(['pid' => $id])
            ->orderBy('id desc')
            ->asArray()
            ->all();

        $comment_id_user_id_list = array_column($list, 'user_id', 'id');
        $comment_id_user_id_list[$info['id']] = $info['user_id'];

        $nick_name_list = UserModel::find()
            ->select('nick_name,id')
            ->where(['in', 'id', $comment_id_user_id_list])
            ->asArray()
            ->all();

        $user_id_name_list = array_column($nick_name_list, 'nick_name', 'id');
        $info['from_name'] = $user_id_name_list[$comment_id_user_id_list[$info['id']]] ?? '';

        array_walk($list, function (&$value) use ($user_id_name_list, $comment_id_user_id_list) {
            $value['from_name'] = $user_id_name_list[$comment_id_user_id_list[$value['id']]] ?? '';
            $value['to_name'] = $user_id_name_list[$comment_id_user_id_list[$value['reply_pid']]] ?? '';
        });

        $response = [
            'info' => $info,
            'list' => $list
        ];

        return Common::response(1, 'success', $response);
    }


    public function actionCreate()
    {
        $model = new RadioCommentModel();
        $post = Yii::$app->request->post();

        $post['create_time'] = date('Y-m-d H:i:s');
        $model->setAttributes($post);
        if ($model->save()) {
            $radio_id = $post['radio_id'];
            RadioModel::updateAllCounters(['comment_num' => 1], ['id' => $radio_id]);
            return Common::response(1, 'success', $model);
        }
        return Common::response(0, 'failure', $model->getErrors());
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->setAttributes(Yii::$app->request->post());

        if ($model->save()) return Common::response(1, 'success', $model);
        return Common::response(0, 'failure', $model->getErrors());
    }

    protected function findModel($id)
    {
        if (($model = RadioCommentModel::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

}
