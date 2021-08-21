<?php

namespace api\models;
use common\models\MessageDynamicModel;

class MessageDynamic extends MessageDynamicModel
{
    public static $article = 1;
    public static $comment = 2;

    public function fields()
    {
        $fields = parent::fields();
        return $fields;
    }
}
