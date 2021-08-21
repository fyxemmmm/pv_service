<?php

namespace api\models;

use common\Bridge;
use common\models\Common;
use common\models\JmbModel;
use common\models\JmbCategoryModel;
use common\models\CollectionsModel;
use common\models\JmbDetailModel;
use common\models\JmbBannerModel;

class Jmb extends JmbModel
{
    public static $userID;

    CONST PERIOD = [
        0 => '永久'
    ];

    CONST TYPE = [
        0 => '电话接通'
    ];

    public function fields()
    {
        $fields = parent::fields();

        $fields['category_name'] = function (){
            $pid = JmbCategoryModel::find()->select('pid')->where(['id'=>$this->jmb_category_id])->asArray()->one();
            return JmbCategoryModel::find()->select('name')->where(['id' => $pid])->scalar() ? : '';
        };

        // 几年品牌
        $fields['brand_year'] = function (){
            preg_match('/\d+/',$this->register_time,$match);
            if(empty($match)) return '';
            $brand_year = $match[0];
            $today_year = date('Y');
            return (string)($today_year - $brand_year);
        };

        $fields['collect'] = function (){  // 是否收藏
            $data = CollectionsModel::find()->where(['item_id' => $this->id,"type"=>1 ,"user_id" => self::$userID])->one();
            return $data ? "1" : "0"; // 是否收藏
        };

        $fields['origin_price'] = function (){
            return JmbDetailModel::find()->select('origin_price')->where(['jmb_id' => $this->id])->scalar();
        };

        $fields['vip_price'] = function (){
            return JmbDetailModel::find()->select('vip_price')->where(['jmb_id' => $this->id])->scalar();
        };

        $fields['is_vip'] = function (){
            return Common::checkVip(self::$userID);
        };

        $fields['period'] = function (){
            $period = JmbDetailModel::find()->select('period')->where(['jmb_id' => $this->id])->scalar();
            return self::PERIOD[$period];
        };

        $fields['type'] = function (){
            $type = JmbDetailModel::find()->select('type')->where(['jmb_id' => $this->id])->scalar();
            return self::TYPE[$type];
        };

        $fields['banner'] = function (){
            return JmbBannerModel::find()->select('image_url')->asArray()->all();
        };

        $fields['share_url'] = function (){
            $bridge = new Bridge();
            $share_url = $bridge->jmb_share_url;
            return $share_url . $this->id;
        };

        $fields['info'] = function(){
            $res = mb_convert_encoding(substr(htmlspecialchars_decode($this->info), 0, 300), 'UTF-8', 'UTF-8');
            return $res;
        };

        return $fields;
    }

}
