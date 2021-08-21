<?php

namespace api\models;
use common\models\ActivityModel;
use common\models\UserModel;
use common\models\ActivityItemModel;
use Yii;

class Activity extends ActivityModel
{
    public function fields()
    {
        $fields = parent::fields();
        unset($fields['content']);
        unset($fields['activity_time_dur']);

//        $my_activity = Yii::$app->request->get('my_activity');
//        if($my_activity){
//            $fields['price'] = function (){
//                $user_id = Yii::$app->params['__web']['user_id'];
//                $idss = ActivityItemSignUp::find()->where(['activity_id' => $this->id])->andWhere(['user_id' => $user_id])->asArray()->one();
//                return $idss ? $idss['price'] : 0;
//            };
//        }

        $fields['avatar_image'] = function (){
            $avatar_image = UserModel::find()->select('avatar_image')->where(['id' => $this->user_id])->scalar();
            return $avatar_image ? : '';
        };

        $fields['creater_name'] = function (){
            $creater_name = UserModel::find()->select('nick_name')->where(['id' => $this->user_id])->scalar();
            return $creater_name ? : '';
        };

        $fields['activity_time'] = function (){
            if(is_null($this->activity_time)) return '';
//            return date('Y-m-d', strtotime($this->activity_time));
            return $this->activity_time;
        };

        $fields['activity_time_end'] = function (){
            if(is_null($this->activity_time_end)) return '';
//            return date('Y-m-d', strtotime($this->activity_time_end));
            return $this->activity_time_end;
        };

        return $fields;
    }

    public function extraFields()
    {
        $fields['content'] = function (){
            return $this->content;
        };

        return $fields;
    }

}
