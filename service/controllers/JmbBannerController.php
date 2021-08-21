<?php

namespace service\controllers;
use common\models\Common;
use common\models\JmbBannerModel;
use yii\data\ActiveDataProvider;
use common\models\JmbImageModel;

class JmbBannerController extends CommonController
{

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'],$actions['view']);
        return $actions;
    }

    public function actionIndex()
    {
        $query = JmbImageModel::find()->where(['type' => 1]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        return $dataProvider;
    }

    public function actionView($id)
    {
        $query = JmbImageModel::find()->where(['type' => 1, 'id'=>$id]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        return $dataProvider;
    }

    public function actionCreate(){
        $post = $this->post();
        $model = new JmbImageModel();
        $model->setAttributes($post);
        if($model->save()) return Common::response(1, '创建成功');
        return Common::response(0, '创建失败', $model->getErrors());
    }

    public function actionUpdate($id){
        $post = $this->post();
        $model = JmbImageModel::find()->where(['id' => $id])->one();
        $model->setAttributes($post);
        if($model->save()) return Common::response(1, '更新成功');
        return Common::response(0, '更新失败', $model->getErrors());
    }

    public function actionDelete($id){
        $model = JmbImageModel::find()->where(['id' => $id])->one();
        if($model){
            $model->delete();
            return Common::response(1, '删除成功');
        }
        return Common::response(0, '删除失败', $model->getErrors());
    }

    /*
     * 获取ad图片
     * */
    public function actionGetAd(){
        $data = JmbImageModel::find()->where(['type' => 2])->asArray()->one();
        return ['items' => [0=>$data]];
    }

    public function actionEditAd(){
        $post = $this->post();
        $model = JmbImageModel::find()->where(['type' => 2])->one();
        if($model){
            $model->setAttributes($post);
            if($model->save()) return Common::response(1, '更新成功');
            return Common::response(0, '更新失败', $model->getErrors());
        }
        return Common::response(0, '未找到相关数据');
    }



    /*
     * * * * * * * * * * * * * * * * * * * * * * * 上面是首页的banner,下面是加盟宝详情的banner
     */


    public function actionGetBanner(){
        $jmb_id = $this->get('jmb_id');
        $query = JmbBannerModel::find()->where(['jmb_id' => $jmb_id]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        return $dataProvider;
    }

    public function actionGetDetail(){
        $id = $this->get('id');
        $query = JmbBannerModel::find()->where(['id' => $id]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        return $dataProvider;
    }

    public function actionEditBanner(){
        $id = $this->post('id');
        $image_url = $this->post('image_url');
        $model = JmbBannerModel::find()->where(['id' => $id])->one();
        $model->image_url = $image_url;
        if($model->save()) return Common::response(1, '更新成功');
        return Common::response(0, '更新失败', $model->getErrors());
    }

    public function actionAddBanner(){
        $jmb_id = $this->post('jmb_id');  // 加盟宝id
        $image_url = $this->post('image_url');
        $model = new JmbBannerModel();
        $model->jmb_id = $jmb_id;
        $model->image_url = $image_url;
        if($model->save()) return Common::response(1, '添加成功');
        return Common::response(0, '添加失败', $model->getErrors());
    }

    public function actionDeleteBanner(){
        $id = $this->post('id');
        $model = JmbBannerModel::findOne($id);
        if($model->delete()) return Common::response(1, '删除成功');
        return Common::response(0, '删除失败', $model->getErrors());

    }


}
