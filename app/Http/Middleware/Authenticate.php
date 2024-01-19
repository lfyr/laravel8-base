<?php

namespace App\Http\Middleware;

use App\Http\Helper\RspHelper;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    use RspHelper;

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param \Illuminate\Http\Request $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (!$request->expectsJson()) {
            return $this->jsonErr(404);
//            return route('login');
        }
    }
}
