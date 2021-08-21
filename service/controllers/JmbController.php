<?php

namespace service\controllers;
use common\Helper;
use common\models\JmbDetailModel;
use yii\data\ActiveDataProvider;
use common\models\JmbModel;
use service\models\Jmb;
use common\models\Common;

class JmbController extends CommonController
{

    CONST INVEST = [  // 投资搜索
        '1-10万',
        '10-20万',
        '20-30万',
        '30-40万',
        '40-50万',
        '50万以上',
    ];


    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'],$actions['view']);
        return $actions;
    }

    public function actionIndex()
    {
        $query = JmbModel::find()->select('id,name,image_url,buy_num,status')->where(['del' => 0])->orderBy('id desc');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $dataProvider;
    }

    public function actionView($id)
    {
        $query = Jmb::find()->where(['id' => $id]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $dataProvider;
    }

    public function actionCreate()
    {
        $post = $this->post();
        if(isset($post['register_time'])){
            $match = preg_match('/\d+年/',$post['register_time']);
            if(!$match) return Common::response(0, '品牌注册时间请正确填写 如2019年');
        }

        $chunk_arr = [
            'type',
            'period',
            'origin_price',
            'vip_price',
            'contact_mobile',
            'contact_person',
            'contact_person_job'
        ];
        list($jmb_data, $jmb_detail_data) = Helper::chunkData($post,$chunk_arr);
        $jmb_model = new JmbModel();
        $transaction = JmbModel::getDb()->beginTransaction();
        try {
            $info = $jmb_data['info'] ?? ''; // 富文本内容
            $jmb_data['info'] = htmlspecialchars($info);
            $jmb_data['create_time'] = date('Y-m-d H:i:s');
            $jmb_model->setAttributes($jmb_data);
            $jmb_model->save();

            $jmb_detail_data['jmb_id'] =$jmb_model->id;
            $jmb_detail_model = new JmbDetailModel();
            $jmb_detail_model->setAttributes($jmb_detail_data);
            $jmb_detail_model->save();

            $transaction->commit();
        } catch(\Exception $e) {
            $transaction->rollBack();
            return Common::response(0, 'failure' . $e->getMessage(), $e->getMessage());
        } catch(\Throwable $e) {
            $transaction->rollBack();
            return Common::response(0, 'failure1' . $e->getMessage(), $e->getMessage());
        }
        return Common::response(1, 'success');
    }


    public function actionUpdate($id){
        $post = $this->post();
        $chunk_arr = [
            'type',
            'period',
            'origin_price',
            'vip_price',
            'contact_mobile',
            'contact_person',
            'contact_person_job'
        ];

        if(isset($post['register_time'])){
            $match = preg_match('/\d+年/',$post['register_time']);
            if(!$match) return Common::response(0, '品牌注册时间请正确填写 如2019年');
        }

        list($jmb_data, $jmb_detail_data) = Helper::chunkData($post,$chunk_arr);
        $jmb_model = JmbModel::find()->where(['id' => $id])->one();

        $transaction = JmbModel::getDb()->beginTransaction();
        try {
            $info = $jmb_data['info'] ?? ''; // 富文本内容
            $jmb_data['info'] = htmlspecialchars($info);
            $jmb_data['update_time'] = date('Y-m-d H:i:s');
            $jmb_model->setAttributes($jmb_data);
            $jmb_model->save();

            $jmb_detail_model = JmbDetailModel::find()->where(['jmb_id' => $id])->one();
            $jmb_detail_model->setAttributes($jmb_detail_data);
            $jmb_detail_model->save();

            $transaction->commit();
        } catch(\Exception $e) {
            $transaction->rollBack();
            return Common::response(0, 'failure', $e->getMessage());
        } catch(\Throwable $e) {
            $transaction->rollBack();
            return Common::response(0, 'failure', $e->getMessage());
        }
        return Common::response(1, 'success');
    }


    public function actionDelete($id){
        $jmb_model = JmbModel::find()->where(['id' => $id])->one();
        try {
            $jmb_model->status = 0;
            $jmb_model->del = 1;
            $jmb_model->delete_time = date('Y-m-d H:i:s');
            $jmb_model->save();
        } catch(\Exception $e) {
            // pass
        }
        return Common::response(1, 'success');
    }

    /*
     * 获取投资金额下拉列表
     * */
    public function actionGetInvestPrice(){
        $list = [];
        foreach (self::INVEST as $v){
            $list[] = ['price' => $v];
        }
        return $list;
    }


}


