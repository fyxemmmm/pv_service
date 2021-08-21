<?php

namespace service\controllers;
use common\models\Common;
use common\models\GoodsModel;
use common\models\GoodsSpecificationModel;

class GoodsSpecificationController extends CommonController
{

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'],$actions['view']);
        return $actions;
    }
/*
    public function actionIndex()
    {
        $goods_id = $this->get('goods_id');
        $query = GoodsSpecificationModel::find()->where(['goods_id' => $goods_id])->orderBy('id desc');
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        return $dataProvider;
    }
*/

    public function actionIndex()
    {
        $goods_id = $this->get('goods_id');
        $goods_info = GoodsModel::find()->where(['id' => $goods_id])->one();
        $main_specification_name = $goods_info['main_specification_name'];
        $second_specification_name = $goods_info['second_specification_name'];
        $specification = GoodsSpecificationModel::find()->select('main_specification,second_specification,image_url,purchasing_cost,original_cost,after_discount_cost')->where(['goods_id' => $goods_id])->asArray()->all();

        $pics = [];
        foreach ($specification as $k=>$s){
            foreach ($pics as $v){
                if ($v['main_specification'] == $s['main_specification']) continue 2;
            }

            $main_specification = $s['main_specification'];
            $image_url = $s['image_url'];
            $pics[] = ['main_specification' => $main_specification, 'image_url' => $image_url];
        }

        $res = [
            'goods_id' => $goods_id,
            'main_specification_name' => $main_specification_name,
            'second_specification_name' => $second_specification_name,
            'specification' => $specification,
            'pics' => $pics
        ];

        return $res;
    }

/*
    public function actionView($id)
    {
        $query = GoodsSpecificationModel::find()->where(['id' => $id]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        return $dataProvider;
    }
*/

    public function actionCreate()
    {
        $specifications = $this->post('specification');
        $pics = $this->post('pics');
        $goods_id = $this->post('goods_id');
        $main_specification_name = $this->post('main_specification_name') ?? '';
        $second_specification_name = $this->post('second_specification_name') ?? '';

        if(empty($main_specification_name)) return Common::response(0, '主规格名称不能为空');

        $transaction = GoodsSpecificationModel::getDb()->beginTransaction();
        try {
            $goods_model = GoodsModel::find()->where(['id' => $goods_id])->one();
            $goods_model->main_specification_name = $main_specification_name;
            $goods_model->second_specification_name = $second_specification_name;
            $goods_model->save();

            foreach ($specifications as $specification){
                $main_specification = $specification['main_specification'] ?? '';
                $second_specification = $specification['second_specification'] ?? '';

                if(empty($main_specification)) return Common::response(0,'主规格必填');
                $name = empty($second_specification) ? $main_specification : $main_specification . " " . $second_specification;

                $purchasing_cost = $specification['purchasing_cost'] ?? 0;
                if(!is_numeric($purchasing_cost) || $purchasing_cost <= 0) return Common::response(0,'请填写正确的进货价');
                $goods_info = GoodsModel::find()->select('good_tax,logistics_fee,discount,profitable_rate,discount')->where(['id' => $goods_id])->asArray()->one();
                list($original_cost, $after_discount_cost) = Common::calcPrice($purchasing_cost, $goods_info);

                $attributes = [
                    'goods_id' => $goods_id,
                    'name' => $name,
                    'purchasing_cost' => $purchasing_cost,
                    'main_specification' => $main_specification,
                    'second_specification' => $second_specification,
                    'original_cost' => $original_cost,
                    'after_discount_cost' => $after_discount_cost,
                ];

                $model = new GoodsSpecificationModel();
                $model->setAttributes($attributes);
                $model->save();
            }

            foreach ($pics as $pic){
                $gs_model = GoodsSpecificationModel::find()->where(['goods_id' => $goods_id])->andWhere(['main_specification' => $pic['main_specification']])->all();
                foreach ($gs_model as $gs_m){
                    $gs_m->image_url = $pic['image_url'];
                    $gs_m->save();
                }
            }

            $transaction->commit();
            return Common::response(1, 'success');

        } catch(\Exception $e) {
            $transaction->rollBack();
            return Common::response(0, '操作失败', $e->getMessage());
        } catch(\Throwable $e) {
            $transaction->rollBack();
            return Common::response(0, '操作失败', $e->getMessage());
        }
    }

    // 更新规格
    public function actionUpdateSp(){
        $goods_id = $this->post('goods_id');
        $specifications = $this->post('specification');
        $pics = $this->post('pics');
        $main_specification_name = $this->post('main_specification_name') ?? '';
        $second_specification_name = $this->post('second_specification_name') ?? '';

        if(empty($main_specification_name)) return Common::response(0, '主规格名称不能为空');

        $transaction = GoodsSpecificationModel::getDb()->beginTransaction();
        try {
            $goods_model = GoodsModel::find()->where(['id' => $goods_id])->one();
            $goods_model->main_specification_name = $main_specification_name;
            $goods_model->second_specification_name = $second_specification_name;
            $goods_model->save();

            $goods_sp_model = GoodsSpecificationModel::find()->where(['goods_id' => $goods_id])->all();
            foreach ($goods_sp_model as $s_model){
                $s_model->delete();
            }

            foreach ($specifications as $specification){
                $main_specification = $specification['main_specification'] ?? '';
                $second_specification = $specification['second_specification'] ?? '';

                if(empty($main_specification)) return Common::response(0,'主规格必填');
                $name = empty($second_specification) ? $main_specification : $main_specification . " " . $second_specification;

                $purchasing_cost = $specification['purchasing_cost'] ?? 0;
                if(!is_numeric($purchasing_cost) || $purchasing_cost <= 0) return Common::response(0,'请填写正确的进货价');
                $goods_info = GoodsModel::find()->select('good_tax,logistics_fee,discount,profitable_rate,discount')->where(['id' => $goods_id])->asArray()->one();
                list($original_cost, $after_discount_cost) = Common::calcPrice($purchasing_cost, $goods_info);

                $attributes = [
                    'goods_id' => $goods_id,
                    'name' => $name,
                    'purchasing_cost' => $purchasing_cost,
                    'main_specification' => $main_specification,
                    'second_specification' => $second_specification,
                    'original_cost' => $original_cost,
                    'after_discount_cost' => $after_discount_cost,
                ];

                $model = new GoodsSpecificationModel();
                $model->setAttributes($attributes);
                $model->save();
            }

            foreach ($pics as $pic){
                $gs_model = GoodsSpecificationModel::find()->where(['goods_id' => $goods_id])->andWhere(['main_specification' => $pic['main_specification']])->all();
                foreach ($gs_model as $gs_m){
                    $gs_m->image_url = $pic['image_url'];
                    $gs_m->save();
                }
            }

            $transaction->commit();
            return Common::response(1, 'success');

        } catch(\Exception $e) {
            $transaction->rollBack();
            return Common::response(0, '操作失败', $e->getMessage());
        } catch(\Throwable $e) {
            $transaction->rollBack();
            return Common::response(0, '操作失败', $e->getMessage());
        }
    }

//    public function actionDelete($id){
//        $model = GoodsSpecificationModel::find()->where(['id' => $id])->one();
//        $goods_id = $model->goods_id;
//        $count = GoodsSpecificationModel::find()->where(['goods_id' => $goods_id])->count();
//        $goods_status = GoodsModel::find()->select('status')->where(['id' => $goods_id])->scalar();
//        if(1 == $count && 1 == $goods_status) return Common::response(0,'请先将商品下架 再删除这最后一个规格!');
//        if($model->delete()) return Common::response(1, 'success');
//        return Common::response(0,'failure', $model->getErrors());
//    }


    /*
     * 已废弃
     * */
//    public function actionUpdate($id)
//    {
//        $post = $this->post();
//        $purchasing_cost = $this->post('purchasing_cost') ?? 0;
//        if(!is_numeric($purchasing_cost) || $purchasing_cost <= 0) return Common::response(0,'请填写正确的进货价');
//        $goods_info = GoodsModel::find()->select('good_tax,logistics_fee,discount,profitable_rate,discount')->where(['id' => $post['goods_id']])->asArray()->one();
//        list($original_cost, $after_discount_cost) = Common::calcPrice($purchasing_cost, $goods_info);
//        $post['original_cost'] = $original_cost;
//        $post['after_discount_cost'] = $after_discount_cost;
//
//        $model = GoodsSpecificationModel::find()->where(['id' => $id])->one();
//        $model->setAttributes($post);
//        if($model->save()) return Common::response(1, 'success');
//        return Common::response(0,'failure', $model->getErrors());
//    }
    /*
     * 废弃
     * */

}
