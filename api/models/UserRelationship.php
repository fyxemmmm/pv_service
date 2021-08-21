<?php

namespace api\models;

use common\models\UserRelationshipModel;
use common\models\UserModel;
use common\models\UserDcOrderModel;

class UserRelationship extends UserRelationshipModel
{

    public function fields()
    {
        $fields = parent::fields();
        unset($fields['id']);
        unset($fields['leader_id']);
        unset($fields['create_time']);

        $fields['nick_name'] = function (){
            $nick_name = UserModel::find()->select('nick_name')->where(['id' => $this->user_id])->scalar() ?: '';
            if(empty($nick_name)){
                $nick_name = UserModel::find()->select('mobile')->where(['id' => $this->user_id])->scalar() ?: '';
                preg_match('/(.{4})(.{4})(.{3})/',$nick_name, $matches);
                try{
                    $return = $matches[1] . '****' . $matches[3];
                    return $return;
                }catch (\Exception $e){
                    return '';
                }
            }
            return $nick_name;
        };

        // 下款额度
        $fields['pay_money'] = function (){
            $moneys = UserDcOrderModel::find()->select('pay_money')->where(['user_id' => $this->user_id, 'status' => 2])->asArray()->all();
            $zs_total_money = array_column($moneys, 'pay_money');
            $zs_total_money = array_sum($zs_total_money);
            return $zs_total_money;
        };

        // 下家数量
        $fields['child_user_count'] = function (){
            return (int)UserRelationshipModel::find()->where(['leader_id' => $this->user_id])->count();
        };

        return $fields;
    }

}
