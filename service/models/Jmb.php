<?php

namespace service\models;

use common\models\JmbModel;
use common\models\JmbDetailModel;
use common\models\JmbCategoryModel;

class Jmb extends JmbModel
{

    CONST PERIOD = [
        0 => '永久'
    ];

    CONST TYPE = [
        0 => '电话接通'
    ];

    public function fields()
    {
        $fields = parent::fields();

        $fields['info'] = function(){
            return mb_convert_encoding(htmlspecialchars_decode($this->info), 'UTF-8', 'UTF-8');
        };

        $fields['type'] = function (){
            return JmbDetailModel::find()->select('type')->where(['jmb_id' => $this->id])->scalar();
        };

        $fields['period'] = function (){
            return JmbDetailModel::find()->select('period')->where(['jmb_id' => $this->id])->scalar();
        };

        $fields['origin_price'] = function (){
            return JmbDetailModel::find()->select('origin_price')->where(['jmb_id' => $this->id])->scalar();
        };

        $fields['vip_price'] = function (){
            return JmbDetailModel::find()->select('vip_price')->where(['jmb_id' => $this->id])->scalar();
        };

        $fields['contact_mobile'] = function (){
            return JmbDetailModel::find()->select('contact_mobile')->where(['jmb_id' => $this->id])->scalar();
        };

        $fields['contact_person'] = function (){
            return JmbDetailModel::find()->select('contact_person')->where(['jmb_id' => $this->id])->scalar();
        };

        $fields['contact_person_job'] = function (){
            return JmbDetailModel::find()->select('contact_person_job')->where(['jmb_id' => $this->id])->scalar();
        };

        $fields['first_cate_id'] = function(){
            $pid = JmbCategoryModel::find()->select('pid')->where(['id' => $this->jmb_category_id])->scalar();
            return (int)JmbCategoryModel::find()->select('id')->where(['id' => $pid])->scalar();
        };



        return $fields;
    }

}
