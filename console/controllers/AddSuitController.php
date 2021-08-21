<?php
namespace console\controllers;

use yii\console\Controller;
use common\Helper;
use common\models\UserModel;
use common\models\Common;


class AddSuitController extends Controller
{
    public $message;

    public function options($actionID)
    {
        return ['message'];
    }

    public function optionAliases()
    {
        return ['m' => 'message'];
    }

    public function actionIndex()
    {
        Helper::genSuitUser(100);
    }


}