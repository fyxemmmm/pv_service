<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "make_money_group".
 *
 * @property int $id
 * @property int $u_id 用户id
 * @property int $type_id 类型id
 * @property string $name 名称
 * @property string $logo logo
 * @property int $nums 群人数
 * @property int $upvote_nums 点赞人数
 * @property string $description 描述
 * @property string $announcement 公告
 * @property string $im_group_id 群组id
 * @property string $create_time 创建时间
 * @property string $update_time 更新时间
 * @property int $join_need_consent 加入需要同意
 * @property int $status 状态
 */
class MakeMoneyGroupModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'make_money_group';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['u_id', 'type_id', 'nums', 'upvote_nums', 'im_group_id', 'join_need_consent', 'status'], 'integer'],
            [['description', 'announcement'], 'string'],
            [['create_time', 'update_time'], 'safe'],
            [['name', 'logo'], 'string', 'max' => 127],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'u_id' => '用户id',
            'type_id' => '类型id',
            'name' => '名称',
            'logo' => 'logo',
            'nums' => '群人数',
            'upvote_nums' => '点赞人数',
            'description' => '描述',
            'announcement' => '公告',
            'im_group_id' => '群组id',
            'create_time' => '创建时间',
            'update_time' => '更新时间',
            'join_need_consent' => '加入需要同意',
            'status' => '状态',
        ];
    }
}
