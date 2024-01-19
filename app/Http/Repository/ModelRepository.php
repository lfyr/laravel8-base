<?php

namespace App\Http\Repository;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ModelRepository
{
    protected $modelClass;

    public function __construct($modelClass)
    {
        $this->modelClass = $modelClass;
    }

    public static function getInstance($modelClass) {
        return new static($modelClass);
    }

    /**
     * @param $data
     * @return int
     */
    public function create(array $data): int
    {
        $model = $this->getModel();
        $bool = $model->forceFill($data)->save();
        return $bool ? $model->getKey() : 0;
    }

    /**
     * @return Model
     */
    private function getModel()
    {
        return new $this->modelClass();
    }

    /**
     * 创建对象
     * @param array $data
     * @return Model
     */
    public function createObject(array $data): Model
    {
        $model = $this->getModel();
        $model->forceFill($data)->save();
        return $model;
    }

    /**
     * 根据Id获取数据
     * @param $key
     * @param string[] $fields
     * @param array $with
     * @return Model | Collection
     */
    public function get($key, $fields = ['*'], $with = [])
    {
        $query = $this->getModel()->query();
        if (!empty($with)) {
            $query->with($with);
        }
        return $query->find($key, $fields);
    }

    /**
     * 条件获取单个数据
     * @param $cond
     * @param string[] $fields
     * @param array $with
     * @param array $orderBy
     * @return mixed
     */
    public function first($cond, $fields = ['*'], $orderBy = [], $with = [])
    {
        $query = $this->getModel()->query();

        $this->inQuery($cond, $query);

        $query->where($cond);

        if (!empty($with)) {
            $query->with($with);
        }

        // 增加排序
        if (!empty($orderBy)) {
            foreach ($orderBy as $column => $direction) {
                if (is_numeric($column) && is_array($direction)) {
                    $query->orderBy(...$direction);
                } else {
                    $query->orderBy($column, $direction);
                }
            }
        }

        return $query->first($fields);
    }

    /**
     * 支持in查询
     * @param $cond
     * @param $query
     */
    private function inQuery(&$cond, $query)
    {
        if (isset($cond['in'])) {
            $isArr = false;
            // 兼容数组in
            foreach ($cond['in'] as $key => $item) {
                if (is_numeric($key) && is_string($item)) {
                    break;
                } else if (is_numeric($key) && is_array($item)) {
                    $query->whereIn(...$item);
                    $isArr = true;
                }
            }
            !$isArr && $query->whereIn(...$cond['in']);
            unset($cond['in']);
        }
    }

    /**
     * 获取多条
     * @param $cond
     * @param string[] $fields
     * @param $with
     * @param array $orderBy
     * @return Collection
     */
    public function many($cond, $fields = ['*'], $orderBy = [], $with = []): Collection
    {
        $query = $this->getModel()->query();

        $this->inQuery($cond, $query);

        $query = $query->where($cond);
        if (!empty($with)) {
            $query->with($with);
        }

        // 增加排序
        if (!empty($orderBy)) {
            foreach ($orderBy as $column => $direction) {
                if (is_numeric($column) && is_array($direction)) {
                    $query->orderBy(...$direction);
                } else {
                    $query->orderBy($column, $direction);
                }
            }
        }

        return $query->select($fields)->get();
    }

    /**
     * @param $key
     * @param $data
     * @return bool
     */
    public function update(int $key, array $data): bool
    {
        $model = $this->getModel();
        return $model->where($model->getKeyName(), $key)->update($data);
    }

    /**
     * @param array $cond
     * @return int
     */
    public function count(array $cond): int
    {
        $model = $this->getModel();
        return $model->where($cond)->count();
    }

    /**
     * @param array $wheres
     * @param array $orderBy
     * @param int $pageSize
     * @param int $pageNum
     * @param array $relations
     * @param string[] $fields
     * @return array
     */
    public function pageList($wheres, int $pageNum, int $pageSize, $orderBy = [], $relations = [], $fields = ['*']): array
    {
        $query = $this->getModel()->query();

        $this->inQuery($wheres, $query);

        $query->where($wheres);

        $total = $query->count();

        // 增加关联数据输出
        if (!empty($relations)) {
            $query->with($relations);
        }

        // 增加排序
        if (!empty($orderBy)) {
            foreach ($orderBy as $column => $direction) {
                if (is_numeric($column) && is_array($direction)) {
                    $query->orderBy(...$direction);
                } else {
                    $query->orderBy($column, $direction);
                }
            }
        }

        $data = $query->forPage($pageNum, $pageSize)
            ->get($fields);
        return [$total, $data];
    }

    /**
     * 扩展方法
     * @param $method
     * @param $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        return $this->getModel()->$method(...$arguments);
    }
}
