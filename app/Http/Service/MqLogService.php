<?php

namespace App\Http\Service;

use App\Http\Repository\MqLogRepository;

class MqLogService
{
    public function getListByCond($params, $pageNum, $pageSize, $orderBy = [], $relations = [], $fields = ['*'])
    {
        return MqLogRepository::pageList($params, $pageNum, $pageSize, $orderBy, $relations, $fields);
    }

    public function getById($key, $fields = ['*'], $with = [])
    {
        return MqLogRepository::get($key, $fields, $with);
    }

    public function getByCond($cond, $fields = ['*'], $orderBy = [], $with = [])
    {
        return MqLogRepository::first($cond, $fields, $orderBy, $with);
    }

    public function addOne($data)
    {
        return MqLogRepository::createObject($data);
    }

    public function updateOne($id, $data)
    {
        return MqLogRepository::update($id, $data);
    }

    public function getListManyByCond($cond, $fields = ['*'], $orderBy = [], $with = [])
    {
        return MqLogRepository::many($cond, $fields, $orderBy, $with);
    }

}
