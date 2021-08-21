<?php

namespace api\models;

use common\models\ArticleCollectModel;
use common\models\ArticleTypeModel;
use common\models\UserModel;
use Yii;
use api\models\Article;
use common\models\ArticleModel;

class ArticleCollect extends ArticleCollectModel
{

    public function fields()
    {
        $fields = parent::fields();

        $fields['author'] = function(){
            $id = ArticleModel::find()->select('creater')->where(['id' => $this->article_id])->scalar();
            return UserModel::find()->select('nick_name')->where(['id'=>$id])->scalar();
        };
        $fields['article_time'] = function(){
            $time =  ArticleModel::find()->select('create_time')->where(['id' => $this->article_id])->scalar();
            return $time ? date('Y-m-d', strtotime($time)) : '';
        };
        $fields['title'] = function(){
            return ArticleModel::find()->select('title')->where(['id' => $this->article_id])->scalar();
        };
        $fields['preview_image'] = function(){
            return ArticleModel::find()->select('preview_image')->where(['id' => $this->article_id])->scalar();
        };
        $fields['desc'] = function(){
            return ArticleModel::find()->select('desc')->where(['id' => $this->article_id])->scalar();
        };
        $fields['comment_num'] = function () {
            return (int) ArticleModel::find()->select('comment_num')->where(['id' => $this->article_id])->scalar();
        };
        $fields['like_num'] = function () {
            return (int) ArticleModel::find()->select('like_num')->where(['id' => $this->article_id])->scalar();
        };
        $fields['type_name'] = function () {
            $type_id = ArticleModel::find()->select('type')->where(['id' => $this->article_id])->scalar();
            return (string) ArticleTypeModel::find()->select('at_name')->where(['at_id' => $type_id])->scalar();
        };

        $fields['avatar_image'] = function () {
            $user = User::findOne($this->user_id);
            return $user ? $user->avatar_image : '';
        };

        return $fields;
    }
}
