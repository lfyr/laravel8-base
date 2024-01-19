<?php

namespace App\Http\Controllers;


use App\Http\Helper\RabbitMQService;

class IndexController extends Controller
{
    public function produce(RabbitMQService $mq)
    {
        set_time_limit(120);
        try {
            for ($i = 1; $i <= 1000; $i++) {
                $data = [
                    "id" => $i,
                    "name" => "msg===" . $i,
                ];
                $mq->producer('product', 'exc_product', 'pus_product', json_encode($data));
            }
        } catch (\Exception $e) {
            return $this->jsonErr(500, $e->getMessage());
        }
        return $this->json(true);
    }
}
