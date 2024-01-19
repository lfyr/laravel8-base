<?php

namespace App\Exceptions;

use App\Http\Helper\RspHelper;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    use RspHelper;

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function renderHttpException(HttpExceptionInterface $e)
    {
        if (is_a($e, AccessDeniedHttpException::class)) {
            return $this->jsonErr(403);
        }

        if (is_a($e, ThrottleRequestsException::class)) {
            return $this->jsonErr(707);
        }

        return $this->jsonErr($e->getStatusCode(), $e->getMessage());
    }

    protected function invalid($request, ValidationException $exception)
    {
        return $this->invalidJson($request, $exception);
    }

    protected function invalidJson($request, ValidationException $exception)
    {
        return $this->jsonErr(502, $exception->validator->errors()->first());
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->is('api/*') || $request->is('adminapi/*')) {
            return $this->jsonErr(602);
        }

        $loginPath = '/login?redirect=%2F';
        if ($request->header('isMobile')) {
            $loginPath = '/pages/login/index';
        }

        return redirect($loginPath);
    }

    protected function prepareJsonResponse($request, Throwable $e)
    {
        if (is_a($e, AccessDeniedHttpException::class)) {
            return $this->jsonErr(403);
        }

        if (is_a($e, ThrottleRequestsException::class)) {
            return $this->jsonErr(707);
        }

        return parent::prepareJsonResponse($request, $e);
    }
}
