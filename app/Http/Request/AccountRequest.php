<?php

namespace App\Http\Request;

class AccountRequest extends BaseFormRequest
{

    /**
     * 用户密码登录
     * @return string[]
     */
    public function login()
    {
        return [
            'email' => 'required',
            'password' => 'required',
        ];
    }

    public function resetPassword()
    {
        return [
            'user_id' => 'required',
        ];
    }

    public function getDetail()
    {
        return [
            'id' => 'required_without:mobile',
            'mobile' => 'required_without:id',
        ];
    }

    public function messages()
    {
        return [
            'email.required' => '账号为必填项',
            'password.required' => '密码为必填项',
        ];
    }

}
