<?php

namespace service\controllers;

use common\models\RadioModel;
use service\models\Radio;
use service\models\RadioSearch;
use common\models\RadioLabelModel;
use common\models\LabelModel;
use Yii;
use common\models\Common;
use yii\web\NotFoundHttpException;
use common\models\RadioParticipantModel;



class RadioController extends CommonController
{

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'], $actions['view']);
        return $actions;
    }

    public function actionIndex()
    {
        $searchModel = new RadioSearch();
        $search['RadioSearch'] = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($search);
        return $dataProvider;
    }

    public function actionView($id)
    {
        $searchModel = new RadioSearch();
        $search['RadioSearch'] = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($search);
        return $dataProvider;
    }

    public function actionCreate()
    {
        $model = new Radio();
        $content = htmlspecialchars($this->post('content'));
        $post = Yii::$app->request->post();
        // 给电台默认的喜欢数
        $rand = rand(0, 50);
        $post['like_num'] = $rand;

        $label_name_arr = $post['label_id_list'] ?? [];  // 传递过来的是label的name
        if(!$post['radio_url']) return Common::response(0, '请上传音频');

        unset($post['label_id_list']);

        $model->setAttributes($post);
        $model->content = $content;
        $model->create_time = Common::generateDatetime();
        if ($model->save()) {
            foreach ($label_name_arr as $name) {
                $label = LabelModel::find()->where(['name' => $name])->one();
                $radio_label_model = new RadioLabelModel();
                if($label){
                    $radio_label_model->setAttributes(['radio_id' => $model->id, 'label_id' => $label->id ?: 0]);
                }else{
                    $label_model = new LabelModel();
                    $label_model->name = $name;
                    $label_model->save();
                    $radio_label_model->setAttributes(['radio_id' => $model->id, 'label_id' => $label_model->id]);
                }
                $radio_label_model->save();
            }

            if(!isset($post['participants'])){
                return Common::response(0, '请填写参与者', $model);
            }
            
            foreach ($post['participants'] as $k=>$user_id){
                $p_model = new RadioParticipantModel();
                $p_model->user_id = $user_id;
                $p_model->radio_id = $model->id;
                $p_model->save();
            }

            return Common::response(1, 'success', $model);
        }
        return Common::response(0, 'failure', $model->getErrors());
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $post = Yii::$app->request->post();
        if(isset($post['status'])){
            $model->setAttributes($post);
            $model->save();
            return Common::response(1, 'success', $model);
        }

        // 1,2,3,4
        $label_name_arr = $post['label_id_list'] ?? [];  // 传递过来的是label的name
        unset($post['label_id_list']);
        RadioLabelModel::deleteAll(['radio_id' => $id]);
        foreach ($label_name_arr as $name) {
            $label = LabelModel::find()->where(['name' => $name])->one();
            $radio_label_model = new RadioLabelModel();
            if($label){
                $radio_label_model->setAttributes(['radio_id' => $id, 'label_id' => $label->id ?: 0]);
            }else{
                $label_model = new LabelModel();
                $label_model->name = $name;
                $label_model->save();
                $label_id = Yii::$app->db->getLastInsertID(); // 获得返回id
                $radio_label_model->setAttributes(['article_id' => $id, 'label_id' => $label_id]);
            }
            $radio_label_model->save();
        }

        if(isset($post['participants'])){
            RadioParticipantModel::deleteAll(['radio_id' => $id]);
            foreach ($post['participants'] as $k=>$user_id){
                $p_model = new RadioParticipantModel();
                $p_model->user_id = $user_id;
                $p_model->radio_id = $model->id;
                $p_model->save();
            }
        }

        $model->setAttributes($post);
        if (null !== $this->post('content')) {
            $model->content = htmlspecialchars($this->post('content'));
        }
        $model->update_time = Common::generateDatetime();
        if ($model->save()) {
            return Common::response(1, 'success', $model);
        }
        return Common::response(0, 'failure', $model->getErrors());
    }


    public function actionDelete($id)
    {
        $res = RadioModel::updateAll(['status' => 0], ['id' => $id]);
        if ($res) return Common::response(1, '删除成功');
        return Common::response(0, '删除失败');
    }

    /**
     * Finds the BusinessModel model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return RadioModel the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = RadioModel::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
