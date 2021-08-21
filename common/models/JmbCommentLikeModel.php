<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "jmb_comment_like".
 *
 * @property int $id
 * @property int $user_id
 * @property int $comment_id
 */
class JmbCommentLikeModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'jmb_comment_like';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'comment_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'comment_id' => 'Comment ID',
        ];
    }
}
