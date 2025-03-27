<?php

namespace App\Traits;

trait ApiResponse
{
    protected function successResponse($data, $message = 'عملیات با موفقیت انجام شد', $status = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    protected function errorResponse($message = 'خطایی رخ داد', $status = 500)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $status);
    }
}
