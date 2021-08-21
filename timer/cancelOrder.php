<?php
Co::set(['hook_flags' => SWOOLE_HOOK_ALL | SWOOLE_HOOK_CURL]);
use common\models\Common;
class CancelOrder
{
    private static $DB_NAME;
    private static $DB_PASSWORD;
    private static $DNS;


    private $_serv;
    /**
     * 待支付 - 处理订单24小时过期
     */
    public function __construct()
    {
        $components = require "../common/config/main-local.php";
        self::$DB_NAME = $components['components']['db']['username'];
        self::$DB_PASSWORD = $components['components']['db']['password'];
        self::$DNS = $components['components']['db']['dsn'];

        $this->_serv = new Swoole\Server("127.0.0.1", 9510);
        $this->_serv->set([
            'daemonize' => 1,
            'worker_num' => 2,
            'open_length_check'     => true,      // 开启协议解析
            'package_length_type'   => 'N',     // 长度字段的类型
            'package_length_offset' => 0,       //第几个字节是包长度的值
            'package_body_offset'   => 4,       //第几个字节开始计算长度
            'package_max_length'    => 81920,  //协议最大长度
        ]);
        $this->_serv->on('Receive', [$this, 'onReceive']);
    }

    public function onReceive($serv, $fd, $fromId, $data)
    {
        $info = unpack('N', $data);  // tcp解包
        $len = $info[1];
        $order_id = (int)substr($data, - $len); // data

        $chan = new Swoole\Coroutine\Channel(1);
        $chan->push(['order_id' => $order_id]);

        Swoole\Timer::after(1000 * 86400, function () use ($chan) {
            $order_id = $chan->pop()['order_id']; // 订单
            $user = self::$DB_NAME;
            $pass = self::$DB_PASSWORD;
            $dsn = self::$DNS;
            try {
                $pdo = new PDO($dsn, $user, $pass); //初始化一个PDO对象
                $pdo->query('set names utf8;');
                $stmt = $pdo->prepare("select * from goods_order where id= {$order_id}");
                $stmt->execute();
                $stmt->setFetchMode(PDO::FETCH_ASSOC);
                $res = $stmt->fetch();
                if($res['status'] == 1){  // 如果该用户的订单是待支付的状态,那么取消他的订单
                    Common::quxiao($order_id);
                    $stmt = $pdo->prepare("update goods_order set status = 5 where id = {$order_id}");
                    $stmt->execute();  // commit
                }
            } catch (\Exception $e) {
                file_put_contents("order_err.log",$e->getMessage()."\n",FILE_APPEND);
            }catch (\Throwable $e){
                file_put_contents("order_err.log",$e->getMessage()."\n",FILE_APPEND);
            }
        });
    }

    /**
     * start server
     */
    public function start()
    {
        $this->_serv->start();
    }
}

$reload = new CancelOrder;
$reload->start();
