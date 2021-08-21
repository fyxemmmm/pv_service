<?php
namespace console\controllers;

use yii\console\Controller;
use api\models\User;
use common\models\HuanXin;
use Yii;

class GenController extends Controller
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
//        $db  = Yii::$app->db;
//        $sql = "select * FROM user WHERE id in (SELECT access_token FROM user) and huanxin_username is null;";
//        $command = $db->createCommand($sql);
//        $res     = $command->queryAll();
//        $huanxinParams = Yii::$app->params['huanxin'];
//        $orgname = $huanxinParams['Orgname'];
//        $appkey = $huanxinParams['AppKey'];
//        $appName = $huanxinParams['appName'];
//        $clientID = $huanxinParams['ClientID'];
//        $clientSecret = $huanxinParams['ClientSecret'];
//        $huanxin = new HuanXin($orgname, $appkey, $appName, $clientID, $clientSecret);
//
//
//        $huanxin->getUserInfo('18964590211');exit;

        $user = new User();
        $res = $user->registerHuanxinUser('1896459121', '111111', 'nicheng');

        var_dump($res);exit;


        foreach ($res as $k=>$v){
            try{
                $user = User::findOne($v['id']);
                $mobile = $v['mobile'];
                $accessToken = $v['access_token'];
                $res = $user->registerHuanxinUser($mobile, $accessToken, $mobile);
                if ($res->duration > 0) {
                    $entities = $res->entities[0];
                    $user->huanxin_uuid = $entities->uuid;
                    $user->huanxin_type = $entities->type;
                    $user->huanxin_created = $entities->created;
                    $user->huanxin_modified = $entities->modified;
                    $user->huanxin_username = $entities->username;
                    $user->huanxin_activated = (int) $entities->activated;
                    $user->huanxin_nickname = $entities->nickname;
                    $user->huanxin_password = $accessToken;
                }
                $user->save();
                echo $v['id']. "\n";
            }catch (\Exception $e){
                echo '出了问题：' . json_encode($v) . "\n";
            }
        }
    }

}