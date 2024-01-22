<?php

namespace App\Http\Controllers;


use App\Http\Helper\RabbitMQService;
use App\Http\Service\MqLogService;

class IndexController extends Controller
{
    public function produce(RabbitMQService $mq, MqLogService $mql)
    {
        set_time_limit(120);
        try {
            for ($i = 1; $i <= 2; $i++) {
                $data = [
                    "id" => $i,
                    "name" => "msg===" . $i,
                ];
                $msgBody = json_encode($data);
                $mql->addOne([
                    "mq_key" => md5($msgBody . "product-mq"),
                    "mq_type" => "product-mq",
                    "mq_body" => $msgBody,
                ]);
                $mq->producer('product', 'exc_product', 'pus_product', $msgBody);
            }
        } catch (\Exception $e) {
            return $this->jsonErr(500, $e->getMessage());
        }
        return $this->json(true);
    }
}
