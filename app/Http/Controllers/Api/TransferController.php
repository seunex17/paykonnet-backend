<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PaystackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class TransferController extends Controller
{
    public function listAllBanks()
    {
        try {
            $response = PaystackService::listAllBanks();

            if (isset($response['status']) && $response['status']) {
                return response()->json($response['data'], ResponseAlias::HTTP_OK);
            }

            return response()->json([
                'message' => 'Can not retrieve banks',
            ], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);

        } catch (\Exception $e) {
            Log::error('Paystack List Banks Exception: '.$e->getMessage());

            return response()->json([
                'message' => 'We encountered some problems please try again later',
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function createBankTransfer(Request $request)
    {
        // TODO: Implement createBankTransfer
        return response()->json(['message' => 'Method not implemented'], ResponseAlias::HTTP_NOT_IMPLEMENTED);
    }

    public function verifyBankAccount(Request $request)
    {
        // TODO: Implement verifyBankAccount
        return response()->json(['message' => 'Method not implemented'], ResponseAlias::HTTP_NOT_IMPLEMENTED);
    }
}
