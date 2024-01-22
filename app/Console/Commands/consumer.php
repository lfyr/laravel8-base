<?php

namespace App\Console\Commands;

use App\Http\Helper\RabbitMQService;
use App\Http\Service\MqLogService;
use Illuminate\Console\Command;

class consumer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:consumer_mq';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'consumer mq-test';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(RabbitMQService $mq, MqLogService $mqLog)
    {

        // 处理业务逻辑
        $mq->consumer("product", function ($msg) use ($mqLog) {

            $mqKey = md5($msg->body . "product-mq");
            $mqLog = $mqLog->getByCond(["mq_key" => $mqKey]);
            // $msgData = json_decode($msg->body, true);
            if ($mqLog) {
                try {
                    // TODO 处理业务

                    // 消费成功之后 修改状态 并确认消费
                    $data = [
                        "status" => 2,
                    ];
                    $mqLog->updateOne($mqLog["id"], $data);
                    $msg->ack();
                } catch (\Exception $e) {

                    // 消费失败 检测是否超过三次失败
                    if ($mqLog['consume_err_num'] < 3) {

                        // 增加失败次数
                        $data = [
                            "consume_err_num" => $mqLog["consume_err_num"] + 1,
                        ];
                    } else {

                        // 清除消息  修改为死信状态
                        $msg->ack();
                        $data = [
                            "status" => 3,
                        ];
                    }
                    $mqLog->updateOne($mqLog["id"], $data);
                }
            }
        });
    }
}
