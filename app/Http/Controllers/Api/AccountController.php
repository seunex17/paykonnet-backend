<?php

/**
 * Copyright (C) ZubDev Digital Media - All Rights Reserved
 *
 * File: AccountController.php
 * Author: Zubayr Ganiyu
 *   Email: <seunexseun@gmail.com>
 *   Website: https://zubdev.net
 * Date: 6/6/26
 * Time: 12:04 PM
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SellPayLater;
use App\Models\VirtualCard;
use App\Models\Wallet;
use App\Services\FlutterwaveService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class AccountController extends Controller
{
    public function wallets(Request $request)
    {
        $wallet = Wallet::where('user_id', $request->user()->id)->first();

        return response()->json([
            'account_number' => $wallet->account_number,
            'bank_name' => $wallet->bank_name,
            'references' => $wallet->references,
            'main_balance' => $request->user()->creditBalance(),
        ], ResponseAlias::HTTP_OK);
    }

    public function virtualCard(Request $request)
    {
        $card = VirtualCard::where('user_id', $request->user()->id)
            ->where('is_active', true)
            ->first();

        return response()->json($card ?? [], ResponseAlias::HTTP_OK);
    }

    public function virtualCardDetail(Request $request, string $cardId)
    {
        $response = FlutterwaveService::fetchVirtualCard($cardId);

        if ($response['status'] == 'success') {
            return response()->json($response['data'], ResponseAlias::HTTP_OK);
        }

        return response()->json([
            'message' => 'Can not load card',
        ], ResponseAlias::HTTP_NOT_FOUND);
    }

    public function virtualCardTransactions(Request $request, string $cardId)
    {
        $response = FlutterwaveService::virtualCardTransactions($cardId);

        if ($response['status'] == 'success') {
            return response()->json($response['data'], ResponseAlias::HTTP_OK);
        }

        return response()->json([
            'message' => 'Can not load card',
        ], ResponseAlias::HTTP_NOT_FOUND);
    }

    public function oweSellPayLater(Request $request)
    {
        $owe = SellPayLater::where('user_id', $request->user()->id)
            ->where('paid', false)
            ->sum('amount');

        return response()->json([
            'amount' => $owe,
        ], ResponseAlias::HTTP_OK);
    }

    public function oweSplHistory(Request $request)
    {
        $data = SellPayLater::where('user_id', $request->user()->id)
            ->where('paid', false)
            ->latest()
            ->take(200)
            ->get();

        return response()->json($data, ResponseAlias::HTTP_OK);
    }
}
