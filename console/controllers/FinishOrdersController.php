<?php
namespace console\controllers;

use common\Config;
use common\models\GoodsOrderModel;
use yii\console\Controller;
use common\models\GoodsLogisticsModel;
use common\models\Common;

class FinishOrdersController extends Controller
{
    public $message;

    public function options($actionID)
    {
        return ['message'];
    }
    
    public function optionAliases()
    {
        return ['m' => 'message'];
    }

    public function actionIndex()
    {
        $time = date('Y-m-d H:i:s', strtotime('-7 days')); // 7天自动收货
        $deliver_info = GoodsOrderModel::find()->select('id')->where(['status' => Config::SHOP_ORDER_HAS_DELIVERED])->asArray()->all();  // 已经发货的商品
        $deliver_ids = array_column($deliver_info, 'id'); // 0=>28

        $goods_logistics_info = GoodsLogisticsModel::find()->select('goods_order_id,receive_time')
                                                           ->where(['in','goods_order_id',$deliver_ids])
                                                           ->andWhere(['<=','create_time',$time])
                                                           ->asArray()
                                                           ->all();

        foreach ($goods_logistics_info as $k=>$v){
            if(!empty($v['receive_time'])) continue;
            $model = GoodsOrderModel::find()->where(['id' => $v['goods_order_id']])->one();
            $model->status = Config::SHOP_ORDER_HAS_FINISHED;
            $model->save();
        }
    }
}
