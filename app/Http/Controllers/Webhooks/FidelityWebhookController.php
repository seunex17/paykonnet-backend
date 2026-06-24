<?php

/**
 * Copyright (C) ZubDev Digital Media - All Rights Reserved
 *
 * File: FidelityWebhookController.php
 * Author: Zubayr Ganiyu
 *   Email: <seunexseun@gmail.com>
 *   Website: https://zubdev.net
 * Date: 6/24/26
 * Time: 7:25 AM
 */

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\OnesignalService;
use Illuminate\Http\Request;

class FidelityWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $data = $request->all();

        if ($data['details']['transaction_type'] == 'collect' && $data['details']['status'] == 'Successful') {
            $txnData = $data['details']['data'];
            $accountNumber = $txnData['craccount'];
            $amount = $txnData['amount'];

            if (($wallet = Wallet::where('account_number', $accountNumber)
                ->with('user')
                ->first()) instanceof Wallet) {
                /** @var User $user */
                $user = $wallet->user;
                $user->creditAdd($amount, 'Wallet Funding');
                $notification = [
                    'contents' => "Hello {$user->firstname}, your Paykonet wallet has been credited with N$amount. This amount is now available for spending.",
                    'title' => 'Your Account has been credited successfully!',
                    'filters' => [
                        [
                            'field' => 'tag',
                            'key' => 'uid',
                            'relation' => '=',
                            'value' => $user->id,
                        ],
                    ],
                ];
                OnesignalService::sendPushNotification($notification);

                Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'credit',
                    'references' => 'Wallet-'.time(),
                    'amount' => $amount,
                    'status' => 'success',
                    'note' => 'Wallet funding',
                ]);
            }
        }
    }
}
