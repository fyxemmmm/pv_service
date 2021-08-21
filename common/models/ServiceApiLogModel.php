<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "service_api_log".
 *
 * @property int $id
 * @property string $app_name
 * @property string $controller
 * @property string $action
 * @property array $response
 * @property string $create_time
 * @property int $type 1user,2admin
 * @property int $user_id
 * @property array $input 用户请求参数
 * @property string $request_type 1get,2post,3put,4delete,5options,6others
 * @property int $status_code 服务器状态码
 * @property int $ip
 */
class ServiceApiLogModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'service_api_log';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['response', 'create_time', 'input'], 'safe'],
            [['type', 'user_id', 'status_code', 'ip'], 'integer'],
            [['app_name', 'controller', 'action'], 'string', 'max' => 25],
            [['request_type'], 'string', 'max' => 10],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'app_name' => 'App Name',
            'controller' => 'Controller',
            'action' => 'Action',
            'response' => 'Response',
            'create_time' => 'Create Time',
            'type' => '1user,2admin',
            'user_id' => 'User ID',
            'input' => '用户请求参数',
            'request_type' => '1get,2post,3put,4delete,5options,6others',
            'status_code' => '服务器状态码',
            'ip' => 'Ip',
        ];
    }
}
