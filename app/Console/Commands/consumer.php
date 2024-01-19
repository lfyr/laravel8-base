<?php

namespace App\Console\Commands;

use App\Http\Helper\RabbitMQService;
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
    public function handle(RabbitMQService $mq)
    {
        // 处理业务逻辑
        $mq->consumer("product", function ($msg) {
            $data = json_decode($msg->body, true);

            // 处理业务

            // 成功的时候ack
            if ($data["id"] == 3 || $data["id"] == 30 || $data["id"] == 300) {
                dump($data["id"] . $data["name"] . "消费失败");
            } else {
                dump($data["id"] . $data["name"] . "已消费");
                $msg->ack();
            }
        });
    }
}
