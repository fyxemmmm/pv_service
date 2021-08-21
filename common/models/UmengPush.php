<?php
namespace common\models;
use Umeng\Android\AndroidUnicast;
use Umeng\Android\AndroidBroadcast;
use Umeng\IOS\IOSUnicast;
use Umeng\IOS\IOSBroadcast;



class UmengPush{
    protected $androidAppkey   = NULL;
    protected $androidAppMasterSecret     = NULL;
    protected $iosAppkey = NULL;
    protected $iosAppMasterSecret = NULL;
    protected $timestamp        = NULL;
    protected $validation_token = NULL;

    public function __construct() {
        $this->timestamp = strval(time());
        /*
         * 安卓的appkey相关
         * */
        $this->androidAppkey = '5d2436d9570df36e6a000bbe';
        $this->androidAppMasterSecret = 'zjev9ewtep5dsb3esogl8mczn4afql7j';

        /*
         * IOS的appkey相关
         * */
        $this->iosAppkey = '5d369a590cafb2f98b000c0f';
        $this->iosAppMasterSecret = 'vgybpgokrm657x3vtkh0jcomhfjax3sx';

    }

    // 消息透传  安卓单播
    public function sendAndroidUnicast($device_tokens, $message){
        try {
//            $device_tokens = 'ArqONgmZDTj62qZST0vMRSWo-LUMuvRKuYri6raG5Sy8';
//            $message = json_encode($message);
            $unicast = new AndroidUnicast();

            $unicast->setAppMasterSecret($this->androidAppMasterSecret);  // appMasterSecret
            $unicast->setPredefinedKeyValue("appkey",           $this->androidAppkey);  // appkey
            $unicast->setPredefinedKeyValue("timestamp",        $this->timestamp);  // 时间戳
            $unicast->setPredefinedKeyValue("device_tokens",    $device_tokens);  // 需要知道用户的device token

            $unicast->setPredefinedKeyValue("display_type",     "message");  // 透传
            $unicast->setPredefinedKeyValue("custom", 'custom'); // 透传消息 可以发送json

            $unicast->setPredefinedKeyValue("production_mode", "true");   // 生产模式必须开启
            $unicast->setExtraField("info", $message);  // 额外参数?
//            print("Sending unicast notification, please wait...\r\n");
            $unicast->send();   //  阻塞
//            print("Sent SUCCESS\r\n");
        } catch (\Exception $e) {
//            pass
//            print("Caught exception: " . $e->getMessage());
        }
    }

    // 安卓广播  _type 暂且没有用
    public function sendAndroidBroadcast($title,$text,$message) {
        try {

            $brocast = new AndroidBroadcast();
            $brocast->setAppMasterSecret($this->androidAppMasterSecret);
            $brocast->setPredefinedKeyValue("appkey",           $this->androidAppkey);
            $brocast->setPredefinedKeyValue("timestamp",        $this->timestamp);
            $brocast->setPredefinedKeyValue("ticker",           "");  // 通知栏提示文字 好像没用?
            $brocast->setPredefinedKeyValue("title",            $title);
            $brocast->setPredefinedKeyValue("text",             $text);
            $brocast->setPredefinedKeyValue("after_open",       "go_app");

            // Set 'production_mode' to 'false' if it's a test device.
            // For how to register a test device, please see the developer doc.
            $brocast->setPredefinedKeyValue("production_mode", "true");
            // [optional]Set extra fields
            $brocast->setExtraField("info", $message);
//            print("Sending broadcast notification, please wait...\r\n");
            $brocast->send();
//            print("Sent SUCCESS\r\n");
        } catch (\Exception $e) {
//            print("Caught exception: " . $e->getMessage());
        }
    }

    // 消息 IOS单播
    public function sendIOSUnicast($device_tokens, $message){
        try {
            if($message['type'] == 4){
                $alert = '新增粉丝';
            }elseif($message['type'] == 2){
                $alert = '收到一条新的评论回复';
            }elseif ($message['type'] == 1){
                $alert = '您的文章收到一条新的评论';
            }elseif ($message['type'] == 5){
                $alert = $message['content'];
            }else{
                $alert = '';
            }

            $unicast = new IOSUnicast();
            $unicast->setAppMasterSecret($this->iosAppMasterSecret);
            $unicast->setPredefinedKeyValue("appkey",           $this->iosAppkey);
            $unicast->setPredefinedKeyValue("timestamp",        $this->timestamp);
            // Set your device tokens here
            $unicast->setPredefinedKeyValue("device_tokens",     $device_tokens);
            $unicast->setPredefinedKeyValue("alert", $alert);

            // Set 'production_mode' to 'true' if your app is under production mode
            $unicast->setPredefinedKeyValue("production_mode", "true");   // IOS 暂时false
            // Set customized fields
            $unicast->setCustomizedField("info", $message);
//            print("Sending unicast notification, please wait...\r\n");
            $unicast->send();
//            print("Sent SUCCESS\r\n");
        } catch (\Exception $e) {
//            print("Caught exception: " . $e->getMessage());
        }
    }

    // IOS 广播   _type 暂且没有用
    public function sendIOSBroadcast($alert,$message){
        try {
            $brocast = new IOSBroadcast();
            $brocast->setAppMasterSecret($this->iosAppMasterSecret);
            $brocast->setPredefinedKeyValue("appkey",           $this->iosAppkey);
            $brocast->setPredefinedKeyValue("timestamp",        $this->timestamp);


            $brocast->setPredefinedKeyValue("alert", $alert);
//            $brocast->setPredefinedKeyValue("description", '描述');
//            $brocast->setPredefinedKeyValue("badge", 0);
//            $brocast->setPredefinedKeyValue("sound", "chime");
            // Set 'production_mode' to 'true' if your app is under production mode
            $brocast->setPredefinedKeyValue("production_mode", "true");   // IOS 测试模式暂时false
            // Set customized fields
            $brocast->setCustomizedField("info", $message);
//            print("Sending broadcast notification, please wait...\r\n");
            $brocast->send();
//            print("Sent SUCCESS\r\n");
        } catch (\Exception $e) {
//            print("Caught exception: " . $e->getMessage());
        }

    }

}