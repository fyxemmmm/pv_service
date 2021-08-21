<?php

namespace service\models;

use common\models\ArticleTypeModel;
use common\models\LabelModel;
use Yii;
use common\models\ArticleModel;
use common\models\ArticleLabelModel;

class Article extends ArticleModel
{
    public function fields()
    {
        $fields = parent::fields();
        $fields['preview_content'] = function(){
            return mb_convert_encoding(substr(htmlspecialchars_decode($this->content), 0, 300), 'UTF-8', 'UTF-8');
        };
        $fields['content'] = function(){
            return mb_convert_encoding(htmlspecialchars_decode($this->content), 'UTF-8', 'UTF-8');
        };
        $fields['type_name'] = function () {
            $type_name = ArticleTypeModel::find()
                ->select('at_name')
                ->where(['at_id' => $this->type])
                ->scalar();
            return $type_name ? $type_name : '';
        };
        return $fields;
    }

    public function extraFields()
    {
        $fields = [];
        $fields['label_id_list'] = function (){
            $data =  ArticleLabelModel::find()->where(['article_id' => $this->id])->asArray()->all();
            if(!empty($data)){
                $label_id_arr =  array_column($data,'label_id');
                $data = LabelModel::find()->where(['in','id',$label_id_arr])->asArray()->all();
                return array_column($data,'name');
            }
            return [];
        };

        return $fields;
    }


}
