<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "work_resume".
 *
 * @property int $user_id 用户id
 * @property int $work_id 工作id
 * @property string $resume_path 简历文件地址
 */
class WorkResumeModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'work_resume';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'work_id'], 'required'],
            [['user_id', 'work_id'], 'integer'],
            [['resume_path'], 'string', 'max' => 127],
            [['user_id', 'work_id'], 'unique', 'targetAttribute' => ['user_id', 'work_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'user_id' => '用户id',
            'work_id' => '工作id',
            'resume_path' => '简历文件地址',
        ];
    }
}
