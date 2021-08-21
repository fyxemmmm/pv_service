<?php

namespace service\models;
use common\models\RadioModel;
use common\models\RadioLabelModel;
use common\models\LabelModel;
use common\models\RadioParticipantModel;
use common\models\UserModel;
use Yii;

class Radio extends RadioModel
{
    public function fields()
    {
        $fields = parent::fields();
        unset($fields['content']);
        $fields['participants'] = function (){
            $user_id = RadioParticipantModel::find()->where(['radio_id' => $this->id])->asArray()->all();
            return array_column($user_id, 'user_id');
        };
        
        return $fields;
    }

    public function extraFields()
    {
        $fields['content'] = function (){
            return mb_convert_encoding(htmlspecialchars_decode($this->content), 'UTF-8', 'UTF-8');
        };

        $fields['label_id_list'] = function (){
            $data =  RadioLabelModel::find()->where(['radio_id' => $this->id])->asArray()->all();
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
