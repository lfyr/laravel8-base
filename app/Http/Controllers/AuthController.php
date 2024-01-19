<?php

namespace App\Http\Controllers;

use App\Http\Request\AccountRequest;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
//        $this->middleware('auth:api', ['except' => ['login', 'register', 'refresh']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\Response
     */
    public function login(AccountRequest $request)
    {
        //$credentials = request(['email', 'password']);
        $email = $request->input('email');
        $password = $request->input('password');
        $credentials = ["email" => $email, "password" => $password];
        if (!$token = auth('api')->attempt($credentials)) {
            return $this->jsonErr(['error' => 'Unauthorized'], 401);
        }
        return $this->respondWithToken($token);
    }


//$email = $request->input('email');
//$password = $request->input('password');
//$credentials = [$email, $password];
//dd($credentials);
//if (!$token = auth('api')->attempt($credentials)) {
//return $this->jsonErr(['error' => 'Unauthorized'], 401);
//}
//return $this->respondWithToken($token);
    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return \Illuminate\Http\Response
     */
    protected function respondWithToken($token)
    {
        return $this->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth('api')->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth('api')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\Response
     */
    public function refresh()
    {
        return $this->respondWithToken(auth('api')->refresh());
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function register(Request $request)
    {
        $email = $request->input("email");
        $password = $request->input("password");

        $data = [
            'name' => $email,
            'email' => $email,
            'password' => makePassword($password),
        ];

        try {
            $res = User::create($data);
        } catch (Exception $e) {
            return $this->jsonErr(500, $e->getMessage());
        }

        return $this->json($res);
    }
}
