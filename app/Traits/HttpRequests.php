<?php

namespace App\Traits;

trait HttpRequests
{
    public function success($data, $message = null , $code = 200) {
        return response()->json([
            "data" => $data,
            "message" => $message,
        ], $code);
    }
}
