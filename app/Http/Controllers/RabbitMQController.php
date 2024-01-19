<?php
// rabbitmq 操作类
namespace App\Http\Controllers;

use AMQPConnection;

class RabbitMQController extends Controller
{
    // 配置变量
    public $configs = array(
        'host' => 'localhost',
        'port' => '5672',
        'login' => 'guest',
        'password' => 'guest',
        'vhost' => '/'
    );
    public $exchange_name = 'ex_q_def';// 交换机名称
    public $queue_name = 'ex_q_def';// 队列名称
    public $route_key = '';// 路由key的名称
    public $durable = true;// 持久化，默认true
    public $autodelete = false;// 自动删除

    // 内部通用变量
    private $_conn = null;
    private $_exchange = null;
    private $_channel = null;
    private $_queue = null;

    // 构造函数
    public function __construct()
    {
        // 初始化队列
        $this->init();
    }

    // 配置rabbitmq
    public function set_configs($configs)
    {
        // 初始化配置
        if (!is_array($configs)) {
            echo 'configs is not array.';
        }
        if (!($configs['host'] && $configs['port'] && $configs['login'] && $configs['password'])) {
            echo 'configs is empty.';
        }
        if (!isset($configs['vhost'])) {// 没有vhost元素，给出默认值
            $configs['vhost'] = '/';
        } else {
            if (empty($configs['vhost'])) {// 有vhost元素，但是值为空，给出默认值
                $configs['vhost'] = '/';
            }
        }
        $this->configs = $configs;
    }

    // 初始化rabbitmq
    public function init()
    {
        if (!$this->_conn) {
            $this->_conn = new AMQPConnection($this->configs);// 创建连接对象
            if (!$this->_conn->connect()) {
                echo "Cannot connect to the broker \n ";
                exit(0);
            }
        }

        // 创建channel
        $this->_channel = new AMQPChannel($this->_conn);
    }

    // 创建队列（为了保证正常订阅，避免消息丢失，生产者和消费则都要尝试创建队列：交换机和队列通过路由绑定一起）
    public function create_queue($exchange_name='', $route_key='', $queue_name='')
    {
        if ($exchange_name != '') {
            // 队列名参数可以省略，默认与交换机同名
            $this->exchange_name = $exchange_name;// 更新交换机名称
            $this->queue_name = $exchange_name;// 更新队列名称
        }
        if ($route_key != '') $this->route_key = $route_key;// 更新路由
        if ($queue_name != '') $this->queue_name = $queue_name;// 独立更新队列名称

        // 创建exchange交换机
        $this->_exchange = new AMQPExchange($this->_channel);// 创建交换机
        $this->_exchange->setType(AMQP_EX_TYPE_DIRECT);// 设置交换机模式为direct
        if ($this->durable) {
            $this->_exchange->setFlags(AMQP_DURABLE);// 设置是否持久化
        }
        if ($this->autodelete) {
            $this->_exchange->setFlags(AMQP_AUTODELETE);// 设置是否自动删除
        }
        $this->_exchange->setName($this->exchange_name);// 设置交换机名称
        $this->_exchange->declare();

        // 创建queue队列
        $this->_queue = new AMQPQueue($this->_channel);
        if ($this->durable) {
            $this->_queue->setFlags(AMQP_DURABLE);// 设置是否持久化
        }
        if ($this->autodelete) {
            $this->_queue->setFlags(AMQP_AUTODELETE);// 设置是否自动删除
        }
        $this->_queue->setName($this->queue_name);// 设置队列名称
        $this->_queue->declare();// 完成队列的定义

        // 将queue和exchange通过route_key绑定在一起
        $this->_queue->bind($this->exchange_name, $this->route_key);
    }

    // 生产者，向队列交换机发送消息
    public function send($msg, $exchange_name='', $route_key='', $queue_name='')
    {
        $this->create_queue($exchange_name, $route_key, $queue_name);// 创建exchange和queue根据route_key绑定一起
        // 消息处理
        if (is_array($msg)) {
            $msg = json_encode($msg);// 将数组类型转换成JSON格式
        } else {
            $msg = trim(strval($msg));// 简单处理一下要发送的消息内容
        }

        // 生产者推送消息进队列时，只能将消息推送到交换机exchange中
        if ($this->durable) {
            $this->_exchange->publish($msg, $this->route_key, AMQP_NOPARAM, array('delivery_mode'=>2));// delivery_mode 2持久化 1非持久化;AMQP_NOPARAM表示无参数
        } else {
            $this->_exchange->publish($msg, $this->route_key);
        }
    }

    // 消费者，从队列中获取数，消费队列（订阅）
    public function run($fun_name, $exchange_name='', $route_key='', $queue_name='', $autoack=false)
    {
        if (!$fun_name) return false;// 没有返回函数，或者队列不存在
        $this->create_queue($exchange_name, $route_key, $queue_name);
        // 订阅消息
        while (true) {
            if ($autoack) {
                $this->_queue->consume($fun_name, AMQP_AUTOACK);// 自动应答
            } else {
                $this->_queue->consume($fun_name);// 需要手动应答
            }
        }
    }

    // 消费者，从队列中获取数，消费队列（主动获取）
    public function get($exchange_name='', $route_key='', $queue_name='', $autoack=false)
    {
        $this->create_queue($exchange_name, $route_key, $queue_name);
        // 主动获取消息
        if ($autoack) {
            $msg = $this->_queue->get(AMQP_AUTOACK);// 自动应答
        } else {
            $msg = $this->_queue->get();// 需要手动应答
        }
        return ['msg'=>$msg, 'queue'=>$this->_queue];
    }
}


// 生产者推送
$rmq = new RabbitMQController;
for ($i = 0; $i < 10; $i++) {
    echo 'test_consume_' . $i .'<br />';
    $rmq->send('test_consume_' . $i, 'test_consume');
}

for ($i = 0; $i < 10; $i++) {
    echo 'get_msg_'.$i.'<br />';
    $rmq->send('get_msg_' . $i, 'test_get');
}

echo 'send ok ! ' . date('Y-m-d H:i:s');


// 消费者consume
$rmq = new RabbitMQController;

$s = $rmq->run('processMessage', 'test_consume');

function processMessage($envelope, $queue) {
    $msg = $envelope->getBody();
    sleep(1);  //sleep1秒模拟任务处理
    echo $msg."\n"; //处理消息
    $queue->ack($envelope->getDeliveryTag()); //手动发送ACK应答
}


// 消费者get
$rmq = new RabbitMQController;

$r = $rmq->get('test_get');

echo $r['msg']->getBody();// 取到的消息
$r['queue']->ack($r['msg']->getDeliveryTag());// 手动反馈，删除消费的消息
