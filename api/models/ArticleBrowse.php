<?php

namespace api\models;

use common\models\ArticleBrowseModel;
use Yii;
use api\models\Article;
use common\models\ArticleModel;

class ArticleBrowse extends ArticleBrowseModel
{

    public function fields()
    {
        $fields = parent::fields();

        $fields['title'] = function(){
            $title = ArticleModel::find()->select('title')->where(['id' => $this->article_id])->scalar();
            return $title ? $title : '';
        };
        $fields['preview_image'] = function(){
            $preview_image = ArticleModel::find()->select('preview_image')->where(['id' => $this->article_id])->scalar();
            return $preview_image ? $preview_image : '';
        };
        $fields['desc'] = function(){
            $desc = ArticleModel::find()->select('desc')->where(['id' => $this->article_id])->scalar();
            return $desc ? $desc : '';
        };

        $fields['avatar_image'] = function () {
            $user = User::findOne($this->user_id);
            return $user ? $user->avatar_image : '';
        };

        return $fields;
    }
}
