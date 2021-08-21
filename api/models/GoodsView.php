<?php

namespace api\models;

use common\models\Common;
use common\models\GoodsModel;
use common\models\GoodsSpecificationModel;

class GoodsView extends GoodsModel
{
    public function fields()
    {
        $fields = parent::fields();
        unset($fields['is_recommand']);
        unset($fields['good_tax']);
        unset($fields['profitable_rate']);
        unset($fields['status']);
        unset($fields['create_time']);
        unset($fields['update_time']);

        $fields['dis_count'] = function (){
            return $this->discount;
        };

        $fields['detail_content'] = function (){
            return mb_convert_encoding(htmlspecialchars_decode($this->detail_content), 'UTF-8', 'UTF-8');
        };

        // 能否享受打折
        $fields['can_discount'] = function (){
            $user_id = \Yii::$app->params['__web']['user_id'];
            $can_discount = Common::checkShopDiscount($user_id, $this->id);
            return $can_discount;
        };

        $fields['specifications'] = function (){
            $s_data =  GoodsSpecificationModel::find()->select('id,name,image_url,original_cost,after_discount_cost')->where(['goods_id' => $this->id])->asArray()->all();
            $data_key = array_column($s_data,'original_cost');
            array_multisort($data_key,SORT_ASC, $s_data);
            return $s_data;
        };

        $fields['invite_url'] = function (){
            $user_id = \Yii::$app->params['__web']['user_id'];
            if(empty($user_id)) return "";
            return Common::getInviteUrl($user_id);
        };


        $fields['specifications_arr'] = function (){
            $sp_data = GoodsSpecificationModel::find()->select('id,main_specification,second_specification,image_url,original_cost,after_discount_cost')->where(['goods_id' => $this->id])->asArray()->all();

            if(!empty($sp_data[0]['second_specification'])){ //双规格
                $arr = [];
                foreach ($sp_data as $k=>$v){
                    if(!isset($arr[$v['main_specification']])){
                        $arr[$v['main_specification']] = [];
                    }
                    $arr[$v['main_specification']][] = [
                        'id' => $v['id'], // 规格id
                        'second_specification' => $v['second_specification'],
                        'image_url' => $v['image_url'],
                        'original_cost' => $v['original_cost'],
                        'after_discount_cost' => $v['after_discount_cost'],
                    ];
                }
                $res = [];
                $i = 0;
                foreach ($arr as $main_specification => $second_specification_arr){
                    $res[$i]['main_specification'] = $main_specification;
                    $res[$i]['image_url'] = $second_specification_arr[0]['image_url'] ?? '';

                    // 排序
                    $data_key = array_column($second_specification_arr,'original_cost');
                    array_multisort($data_key,SORT_ASC, $second_specification_arr);

                    $res[$i]['sub'] = $second_specification_arr;
                    $i ++;
                }
                return $res;
            }else {  // 单规格
                $data_key = array_column($sp_data,'original_cost');
                array_multisort($data_key,SORT_ASC, $sp_data);
                return $sp_data;
            }
        };


        return $fields;
    }

}
