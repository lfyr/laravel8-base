<?php

namespace App\Http\Request;

use Illuminate\Foundation\Http\FormRequest;

class BaseFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $method = $this->route()->getActionMethod();

        // 根据方法对不同的请求进行验证 message可共用
        if (method_exists($this, $method)) {
            return call_user_func([$this, $method]);
        }
        return [];
    }

    /**
     * 重写获取参数方法，若传入的值为null， 也使用默认值
     * @param null $key
     * @param null $default
     * @return array|string|null
     */
    public function input($key = null, $default = null)
    {
        $paramValue = parent::input($key, $default);
        return !is_null($paramValue) ? $paramValue : $default;
    }
}
