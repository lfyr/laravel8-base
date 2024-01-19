<?php


namespace App\Http\Service;


use App\Models\User;

trait UseService
{

    /**
     * @return int
     */
    public function getCurrentUserId()
    {
        return $this->getCurrentUser()->getKey();
    }

    /**
     * @return User
     */
    public function getCurrentUser()
    {
        return request()->user();
    }

}