<?php

namespace api\models;

use common\models\JmbCommentModel;
use common\models\UserModel;
use common\Helper;
use common\models\JmbCommentLikeModel;

class JmbComment extends JmbCommentModel
{
    public static $userId;

    public function fields()
    {
        $fields = parent::fields();

        $fields['nick_name'] = function (){
            return UserModel::find()->select('nick_name')->where(['id' => $this->user_id])->scalar() ? : '';
        };

        $fields['user_name'] = function (){ // 仅仅是为了兼容前端的模板
            return UserModel::find()->select('nick_name')->where(['id' => $this->user_id])->scalar() ? : '';
        };

        $fields['avatar_image'] = function (){
            return UserModel::find()->select('avatar_image')->where(['id' => $this->user_id])->scalar() ? : '';
        };

        $fields['time_before'] = function (){
            return Helper::transferTime($this->create_time);
        };

        $fields['like'] = function (){
            return JmbCommentLikeModel::find()->where(['user_id' => self::$userId, 'comment_id' => $this->id])->one() ? 1 : 0;
        };

        $fields['like_count'] = function (){
            return JmbCommentLikeModel::find()->where(['comment_id' => $this->id])->count();
        };

        $fields['child'] = function (){
            $query = JmbComment::find()->where(['pid' => $this->id]);
            $child_info = $query->orderBy('id desc')->asArray()->all();
            if(empty($child_info)) return [];
            $helper = new Helper();
            foreach ($child_info as $k=>&$v){
                if(is_array($v)) $v = array_map([$helper, 'strToInt'], $v);
                $v['time_before'] = Helper::transferTime($v['create_time']);
                $data = UserModel::find()->select('avatar_image,nick_name')->where(['id' => $v['user_id']])->asArray()->one();
                $v['avatar_image'] = $data['avatar_image'] ?? '';
                $v['nick_name'] = $data['nick_name'] ?? '';
                $v['like'] = JmbCommentLikeModel::find()->where(['user_id' => self::$userId, 'comment_id' => $v['id']])->scalar() ? 1 : 0;
                $v['like_count'] = (int)JmbCommentLikeModel::find()->where(['comment_id' => $v['id']])->count();
                $reply_user = JmbComment::find()->where(['id'=>$v['reply_pid']])->one();
                if($reply_user){
                    $id = $reply_user->user_id;
                    $reply_user = UserModel::find()->select('id,nick_name')->where(['id' => $id])->one();
                    $v['replied_user_id'] = $reply_user['id'] ?? '';
                    $v['replied_nick_name'] = $reply_user['nick_name'] ?? '';
                }else{
                    $v['replied_user_id'] =  '';
                    $v['replied_nick_name'] = '';
                }
            }
            return $child_info;
        };

        return $fields;
    }

}
