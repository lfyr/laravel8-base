<?php
namespace App\Http\Repository;

class Repository
{
    protected $modelClass;

    public static function __callStatic($method, $arguments)
    {
        return (new static)->getModelRepository()->$method(...$arguments);
    }

    public function getModelRepository() {
        return ModelRepository::getInstance($this->modelClass);
    }
}