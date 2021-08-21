<?php

namespace api\controllers;

use common\models\Common;
use common\models\MakeMoneyGroupModel;
use Yii;
use common\models\ImMessageUpvoteModel;
use service\controllers\CommonController;
use yii\web\NotFoundHttpException;

/**
 * ImMessageUpvoteController implements the CRUD actions for ImMessageUpvoteModel model.
 */
class ImMessageUpvoteController extends CommonController
{

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'] ,$actions['view']);
        return $actions;
    }

    /**
     * Creates a new ImMessageUpvoteModel model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ImMessageUpvoteModel();
        $post = Yii::$app->request->post();

        $exists = ImMessageUpvoteModel::findOne($post);
        if ($exists) {
            return Common::response(0, 'existed');
        }

        $post['create_time'] = date('Y-m-d H:i:s');
        $model->setAttributes($post);
        if ($model->save()) {
            MakeMoneyGroupModel::updateAllCounters(['upvote_nums' => 1], ['im_group_id' => $post['group_id']]);
            return Common::response(1, 'success');
        }

        return Common::response(0, 'failure');
    }

    /**
     * Displays a single ImMessageUpvoteModel model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView()
    {
        $group_id = $this->get('group_id', '');
        $message_id = $this->get('message_id', '');
        $upvote_user_id = $this->get('upvote_user_id', '');

        $response = [];
        $message_id_list = explode(',', $message_id);
        foreach ($message_id_list as $value) {
            $upvote_count = ImMessageUpvoteModel::find()
                ->where([
                    'group_id' => $group_id,
                    'message_id' => $value
                ])
                ->count();

            $upvote_exists = ImMessageUpvoteModel::find()
                ->where([
                    'group_id' => $group_id,
                    'message_id' => $value,
                    'upvote_user_id' => $upvote_user_id
                ])
                ->one();

            $response[] = [
                'message_id' => $value,
                'upvote_count' => (int) $upvote_count,
                'is_upvoted' => $upvote_exists ? true : false
            ];
        }

        return Common::response(1, 'success', $response);
    }

    /**
     * Deletes an existing ImMessageUpvoteModel model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete()
    {
        $group_id = $this->post('group_id', '');
        $message_id = $this->post('message_id', '');
        $upvote_user_id = $this->post('upvote_user_id', '');

        $res = ImMessageUpvoteModel::deleteAll([
            'group_id' => $group_id,
            'message_id' => $message_id,
            'upvote_user_id' => $upvote_user_id
        ]);

        if ($res) {
            return Common::response(1, 'success');
        } else {
            return Common::response(0, 'failure');
        }
    }

}
