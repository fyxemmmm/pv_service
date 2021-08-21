<?php

namespace service\models;

use common\models\ArticleCommentModel;
use common\models\ArticleModel;
use common\models\UserModel;

/**
 * This is the model class for table "article_comment".
 *
 * @property int $id
 * @property int $article_id
 * @property int $user_id 发表评论的用户id
 * @property string $content 评论内容
 * @property int $pid
 * @property int $reply_pid 回复者的id
 * @property int $like_num 点赞数
 * @property int $child_count 子评论个数
 * @property string $create_time
 * @property string $update_time
 * @property int $del 删除
 * @property int $is_private 私有
 */
class ArticleComment extends ArticleCommentModel
{
    public function fields()
    {
        $fields = parent::fields();

        $fields['nick_name'] = function () {
            $nick_name = UserModel::find()->select('nick_name')->where(['id' => $this->user_id])->scalar();
            return $nick_name ? $nick_name : '';
        };

        $fields['article_name'] = function () {
            $article_name = ArticleModel::find()->select('title')->where(['id' => $this->article_id])->scalar();
            return $article_name ? $article_name : '';
        };
        
        return $fields;
    }
}
