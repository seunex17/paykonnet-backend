<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BankTransfer;
use App\Models\Transaction;
use App\Services\PaystackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
        $user = $request->user();
        $inputs = $request->all();
        $reference = 'Transfer_'.now()->timestamp;
        $inputs['references'] = $reference;
        $amount = $inputs['amount'] ?? null;

        if (! is_numeric($amount)) {
            return response()->json(['message' => 'Invalid amount entered'], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $feePercent = config('site.bankTransferFee', 0);
        $calculatePercent = ($feePercent / 100) * (float) $amount;
        $amountToDebit = (float) $amount + $calculatePercent;

        if (! Hash::check($inputs['pin'] ?? '', $user->transfer_pin)) {
            return response()->json(['message' => 'Transaction pin is invalid'], ResponseAlias::HTTP_BAD_REQUEST);
        }

        if ((float) $amount < 50) {
            return response()->json(['message' => 'Amount can not be less than 50'], ResponseAlias::HTTP_BAD_REQUEST);
        }

        if (! $user->hasCredits($amountToDebit)) {
            return response()->json(['message' => "you don't have enough balance to complete this transfer"], ResponseAlias::HTTP_BAD_REQUEST);
        }

        if (! $request->has('otp')) {
            $user->creditDeduct($amountToDebit);

            if ((float) $user->creditBalance() >= 0) {
                try {
                    $response = PaystackService::createBankTransfer($inputs);
                    if (! empty($response['status'])) {
                        $bankTransfer = BankTransfer::create([
                            'user_id' => $user->id,
                            'references' => $reference,
                            'bank_code' => $inputs['bank_code'] ?? null,
                            'amount' => $amount,
                            'bank_name' => $inputs['bankName'] ?? null,
                            'account_number' => $inputs['account_no'] ?? null,
                            'account_name' => $inputs['accountName'] ?? null,
                            'fee' => $calculatePercent,
                            'remarks' => $inputs['narration'] ?? null,
                        ]);

                        Transaction::create([
                            'user_id' => $user->id,
                            'type' => 'debit',
                            'references' => $reference,
                            'amount' => $amount,
                            'status' => 'success',
                            'note' => 'Transfer '.$amount.' to '.($inputs['accountName'] ?? '').' '.($inputs['bankName'] ?? ''),
                        ]);

                        return response()->json($bankTransfer, ResponseAlias::HTTP_OK);
                    }

                    $user->creditAdd($amountToDebit);

                    return response()->json(['message' => 'Simulator timeout, please try again'], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);

                } catch (\Exception $e) {
                    $user->creditAdd($amountToDebit);

                    return response()->json(['message' => 'We encountered some problems please try again later'], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
                }
            }

            return response()->json(['message' => 'Fraud transaction detected.'], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response()->json(['message' => 'Invalid structural request routing payload parameters'], ResponseAlias::HTTP_BAD_REQUEST);
    }

    public function verifyBankAccount(Request $request)
    {
        $feePercent = config('site.bankTransferFee', 0);

        try {
            $response = PaystackService::resolveBankAccount($request->all());

            if (! empty($response['status'])) {
                return response()->json([
                    'account' => $response->data,
                    'fee' => $feePercent,
                ], ResponseAlias::HTTP_OK);
            }

            return response()->json(['message' => 'Can not resolve bank account'], ResponseAlias::HTTP_BAD_REQUEST);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Can not resolve bank account'], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
