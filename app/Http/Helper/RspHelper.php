<?php


namespace App\Http\Helper;


use Illuminate\Http\Response;

trait RspHelper
{

    /**
     * Respond with a no content response.
     *
     * @return Response
     */
    public function noContent()
    {
        $response = new Response(null);
        // 204
        return $response->setStatusCode(Response::HTTP_NO_CONTENT);
    }

    /**
     * Return a json response.
     * @param array $data
     * @param array $headers
     * @return Response
     */
    public function json($data = [], array $headers = [])
    {
        $status = 200;
        $msg = 'success';

        return new Response(compact('data', 'status', 'msg'), Response::HTTP_OK, $headers);
    }

    /**
     * Return a json response with error message.
     * @param array $data
     * @param array $headers
     * @return Response
     */
    public function jsonErr($status = 200, $msg = null, array $headers = [])
    {
        //get error message from config
        if (is_null($msg)) {
            $msg = config('errmsg')[$status];
        }

        $data = [];

        return new Response(compact('data', 'status', 'msg'), Response::HTTP_OK, $headers);
    }

}
