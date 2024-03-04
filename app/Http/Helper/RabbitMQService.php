<?php

namespace App\Http\Helper;

use App\Http\Service\MqLogService;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQService
{
    protected $conn;

    public function __construct()
    {
        $config = [
            'host' => config("queue.connections.rabbitmq.host"),
            'port' => config('queue.connections.rabbitmq.port'),
            'user' => config('queue.connections.rabbitmq.user'),
            'password' => config('queue.connections.rabbitmq.password'),
            'vhost' => config('queue.connections.rabbitmq.vhost')
        ];
        return $this->conn = new AMQPStreamConnection($config['host'], $config['port'], $config['user'], $config['password'], $config['vhost']);
    }

    /**
     * @param $queue
     * @param $exchange
     * @param $routing_key
     * @param $msgBody
     * @return bool
     * @throws \Exception
     */
    public function producer($queue, $exchange, $routing_key, $msgBody)
    {
        $mqLogService = app(MqLogService::class);

        $mqKey = md5($msgBody . "product-mq");

        // 建立通道
        $channel = $this->conn->channel();

        //开启消息确认
        $channel->confirm_select();

        // 监听数据写入成功
        $channel->set_ack_handler(
            function (AMQPMessage $message) use ($mqLogService, $mqKey) {
                info("投递成功");
                $mqLog = $mqLogService->getByCond(["mq_key" => $mqKey]);
                if ($mqLog) {
                    $data = [
                        "status" => 1,
                    ];
                    $mqLogService->updateOne($mqLog["id"], $data);
                }
            }
        );

        // 监听数据写入失败
        $channel->set_nack_handler(
            function (AMQPMessage $message) use ($mqLogService, $mqKey) {

              info("投递失败");

                $mqLog = $mqLogService->getByCond(["mq_key" => $mqKey]);
                if ($mqLog) {
                    if ($mqLog["retry_deliver_num"] < 3) {

                        // 重新投递
                        $message->nack(true);
                        $data = [
                            "retry_deliver_num" => $mqLog["retry_deliver_num"] + 1,
                        ];
                        $mqLogService->updateOne($mqLog["id"], $data);
                    } else {
                        $data = [
                            "status" => 3,
                        ];
                        $mqLogService->updateOne($mqLog["id"], $data);
                    }
                }
            }
        );

        //声明一个队列，并将队列持久化
        $channel->queue_declare($queue, false, true, false, false);

        // 指定交换机
        $channel->exchange_declare($exchange, 'direct', false, true, false);

        // 绑定队列和类型
        $channel->queue_bind($queue, $exchange, $routing_key);

        $config = [
            'content_type' => 'text/plain',
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT  // 持久化消息
        ];


        // 实例化消息推送
        $message = new AMQPMessage($msgBody, $config);

        //建立消息，并消息持久化
        $channel->basic_publish($message, $exchange, $routing_key);

        // 监听写入
        $channel->wait_for_pending_acks();

        $channel->close();
        $this->conn->close();
        return true;
    }

    /**
     * @param $queue
     * @param $callback
     * @return bool
     * @throws \Exception
     */
    public function consumer($queue, $callback)
    {

        $channel = $this->conn->channel();

        // 消息限流标识处理一个完成才处理下一个
        // $channel->basic_qos(null, 1, null);

        // 从队列中取出消息，并消费
        $channel->basic_consume($queue, '', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $this->conn->close();
        return true;
    }

}

// 生产
//$a = new RabbitMqService();
//$data = [["id" => 1, "name" => "貂蝉"], ["id" => 2, "name" => "王昭君"], ["id" => 3, "name" => "妲己"]];
//foreach ($data as $k => $v) {
//    $a->simpleSend($v);
//}

// 消费
//$a = new RabbitMqService();
//$a->consumer();
