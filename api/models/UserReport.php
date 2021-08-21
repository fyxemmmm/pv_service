<?php

namespace api\models;
use common\models\UserReportModel;
use Yii;

/**
 * This is the model class for table "user_report".
 *
 * @property int $id
 * @property int $user_id 举报者 uid
 * @property int $to_uid 被举报用户的id
 * @property string $create_time
 */
class UserReport extends UserReportModel
{
    public function fields()
    {
        return parent::fields(); // TODO: Change the autogenerated stub
    }
}
