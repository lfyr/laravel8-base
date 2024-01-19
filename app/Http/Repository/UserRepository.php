<?php


namespace App\Http\Repository;


use App\Models\User;

class UserRepository extends Repository
{
    protected $modelClass = User::class;
}