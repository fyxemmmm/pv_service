<?php

namespace api\models;

use common\models\JmbUserModel;
use common\models\JmbModel;
use common\models\JmbDetailModel;

class JmbUser extends JmbUserModel
{
    CONST PERIOD = [
        0 => '永久'
    ];

    CONST TYPE = [
        0 => '电话联系'
    ];

    public function fields()
    {
        $fields = parent::fields();
        unset($fields['user_id'],$fields['status']);

        $fields['id'] = function (){
            return (string)$this->id;
        };

        $fields['jmb_id'] = function (){
            return (string)$this->jmb_id;
        };

        $fields['image_url'] = function (){
            return JmbModel::find()->select('image_url')->where(['id' => $this->jmb_id])->scalar();
        };

        $fields['name'] = function (){
            return JmbModel::find()->select('name')->where(['id' => $this->jmb_id])->scalar();
        };

        $fields['direct_store_num'] = function (){
            return JmbModel::find()->select('direct_store_num')->where(['id' => $this->jmb_id])->scalar();
        };

        $fields['apply_num'] = function (){
            return JmbModel::find()->select('apply_num')->where(['id' => $this->jmb_id])->scalar();
        };

        $fields['main_project'] = function (){
            return JmbModel::find()->select('main_project')->where(['id' => $this->jmb_id])->scalar();
        };

        $fields['register_time'] = function (){
            return JmbModel::find()->select('register_time')->where(['id' => $this->jmb_id])->scalar();
        };

        $fields['location'] = function (){
            return JmbModel::find()->select('location')->where(['id' => $this->jmb_id])->scalar();
        };

        $fields['inital_fee'] = function (){  // 初始预计投入
            return JmbModel::find()->select('inital_fee')->where(['id' => $this->jmb_id])->scalar() . "万";
        };

        $fields['est_mothly_sale'] = function (){  // 预估月销售额
            return JmbModel::find()->select('est_mothly_sale')->where(['id' => $this->jmb_id])->scalar();
        };

        $fields['est_init_investment'] = function (){
            return JmbModel::find()->select('est_init_investment')->where(['id' => $this->jmb_id])->scalar();
        };

        $fields['period'] = function (){
            $period = JmbDetailModel::find()->select('period')->where(['id' => $this->jmb_id])->scalar();
            return self::PERIOD[$period];
        };

        $fields['contact_mobile'] = function (){
            return JmbDetailModel::find()->select('contact_mobile')->where(['id' => $this->jmb_id])->scalar() ?: "";
        };

        $fields['contact_person'] = function (){
            return JmbDetailModel::find()->select('contact_person')->where(['id' => $this->jmb_id])->scalar() ?: "";
        };

        $fields['contact_person_job'] = function (){
            return JmbDetailModel::find()->select('contact_person_job')->where(['id' => $this->jmb_id])->scalar() ?: "";
        };

        $fields['type'] = function (){
            return JmbDetailModel::find()->select('type')->where(['id' => $this->jmb_id])->scalar() ?: "0";
        };

        $fields['contact_way'] = function (){
            $type = JmbDetailModel::find()->select('type')->where(['id' => $this->jmb_id])->scalar();
            return self::TYPE[$type];
        };

        return $fields;
    }

}
