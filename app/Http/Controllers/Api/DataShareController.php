<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class DataShareController extends Controller
{
    public function dataBanks(): JsonResponse
    {
        return $this->notImplemented();
    }

    public function sentDataBanks(): JsonResponse
    {
        return $this->notImplemented();
    }

    public function receivedDataBanks(): JsonResponse
    {
        return $this->notImplemented();
    }

    public function myDataBanks(int $dataBank): JsonResponse
    {
        return $this->notImplemented();
    }

    private function notImplemented(): JsonResponse
    {
        return response()->json([
            'message' => 'Not implemented',
        ], Response::HTTP_NOT_IMPLEMENTED);
    }
}
