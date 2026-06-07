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
use App\Mail\ResetLockscreenPintokenMail;
use App\Mail\ResetTransferPinTokenMail;
use App\Models\SellPayLater;
use App\Models\Transaction;
use App\Models\VirtualCard;
use App\Models\Wallet;
use App\Services\FidelityService;
use App\Services\FlutterwaveService;
use Ichtrojan\Otp\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
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

    public function validateBvn(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'title' => ['required'],
            'phone_no' => ['required', 'regex:/^[0-9]{11}$/'],
            'date_of_birth' => ['required'],
            'gender' => ['required'],
            'address' => ['required'],
            'state' => ['required'],
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors()->first(),
            ], ResponseAlias::HTTP_NOT_FOUND);
        }

        $data = [
            'reference' => time(),
            'phone_no' => $request->phone_no,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'title' => $request->title,
            'residential_address' => $request->address,
            'state_of_residence' => $request->state,
        ];

        $accountNumResponse = FidelityService::openVirtualAccount($data, $request->user());

        if ($accountNumResponse['status'] === 'Successful') {
            $accountData = (object) $accountNumResponse['data']['provider_response'];

            Wallet::updateOrCreate(['user_id' => $request->user()->id], [
                'account_number' => $accountData->account_number,
                'bank_name' => $accountData->bank_name,
                'references' => $accountData->references,
            ]);

            return response()->json([
                'message' => 'Your account number has been generated!',
            ], ResponseAlias::HTTP_OK);
        }

        return response()->json([
            'message' => $accountNumResponse['message'] ?? 'Failed to generate account number',
        ], ResponseAlias::HTTP_NOT_FOUND);
    }

    public function changeAccountPassword(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'old_password' => ['required'],
            'new_password' => ['required', 'min:8'],
            'confirm_password' => ['required', 'same:new_password'],
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors()->first(),
            ], ResponseAlias::HTTP_NOT_FOUND);
        }

        if (! Hash::check($request->old_password, $request->user()->password)) {
            return response()->json([
                'message' => 'Old password does not match',
            ], ResponseAlias::HTTP_NOT_FOUND);
        }

        $user = $request->user();
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'message' => 'Password has been changed successfully!',
        ], ResponseAlias::HTTP_OK);
    }

    public function requestTransferPinResetToken(Request $request)
    {
        $token = (new Otp)->generate($request->user()->email, 'numeric', 4, 10);

        Mail::to($request->user()->email)->send(new ResetTransferPinTokenMail($request->user(), $token->token));

        return response()->json([
            'message' => 'We have sent a token to your email',
        ], ResponseAlias::HTTP_OK);
    }

    public function changeTransferPin(Request $request)
    {
        $otp = (new Otp)->validate($request->user()->email, $request->token);

        if (! $otp->status) {
            return response()->json([
                'message' => 'Reset token is invalid',
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $transferPin = Hash::make($request->pin);
        $user = $request->user();
        $user->transfer_pin = $transferPin;
        $user->save();

        return response()->json([
            'message' => 'Transfer pin has been reset successfully!',
        ], ResponseAlias::HTTP_OK);
    }

    public function requestLockscreenPinResetToken(Request $request)
    {
        $token = (new Otp)->generate($request->user()->email, 'numeric', 4, 10);

        Mail::to($request->user()->email)->send(new ResetLockscreenPintokenMail($request->user(), $token->token));

        return response()->json([
            'message' => 'We have sent a token to your email',
        ], ResponseAlias::HTTP_OK);
    }

    public function changeLockscreenPin(Request $request)
    {
        $otp = (new Otp)->validate($request->user()->email, $request->token);

        if (! $otp->status) {
            return response()->json([
                'message' => 'Reset token is invalid',
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $user = $request->user();
        $user->lockscreen = Hash::make($request->pin);
        $user->save();

        return response()->json([
            'message' => 'Lockscreen pin has been reset successfully!',
        ], ResponseAlias::HTTP_OK);
    }

    public function deleteAccount(Request $request)
    {
        $user = $request->user();
        $user->delete();

        return response()->json([
            'message' => 'Account has been deleted!',
        ], ResponseAlias::HTTP_OK);
    }

    public function createVirtualCard(Request $request)
    {
        $user = $request->user();

        if (! $user->hasCredits(150)) {
            return response()->json([
                'message' => 'Insufficient account balance please refill and try again',
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        if ($request->title == 'Male') {
            $gender = 'M';
        } else {
            $gender = 'F';
        }

        $data = [
            'currency' => 'NGN',
            'amount' => 100,
            'first_name' => $user->firstname,
            'last_name' => $user->lastname,
            'date_of_birth' => $request->dob,
            'email' => $user->email,
            'phone' => $request->phone,
            'title' => $request->title,
            'gender' => $gender,
        ];

        $response = FlutterwaveService::createVirtualCard($data);

        if ($response['status'] != 'success') {
            return response()->json([
                'message' => $response['message'],
            ], ResponseAlias::HTTP_NOT_FOUND);
        }

        VirtualCard::create([
            'user_id' => $user->id,
            'is_active' => true,
            'card_id' => $response['data']['id'],
            'account_id' => $response['data']['account_id'],
            'currency' => $response['data']['currency'],
            'card_pan' => $response['data']['card_pan'],
            'masked_pan' => $response['data']['masked_pan'],
            'city' => $response['data']['city'],
            'state' => $response['data']['state'],
            'address' => $response['data']['address_1'],
            'cvv' => $response['data']['cvv'],
            'expiration' => $response['data']['expiration'],
            'card_type' => $response['data']['card_type'],
            'name_on_card' => $response['data']['name_on_card'],
        ]);

        $user->creditDeduct(150, 'Create virtual card');

        Transaction::create([
            'user_id' => $user->id,
            'type' => 'debit',
            'references' => time(),
            'amount' => 150,
            'status' => 'success',
            'note' => 'Virtual card insurance fee',
            'source_table' => 'virtual_cards',
        ]);

        return response()->json([
            'message' => 'Card created successfully',
        ], ResponseAlias::HTTP_OK);
    }

    public function blockVirtualCard(Request $request, string $id)
    {
        $response = FlutterwaveService::blockVirtualCard($id);

        if ($response['status'] != 'success') {
            return response()->json([
                'message' => $response['message'],
            ], ResponseAlias::HTTP_NOT_FOUND);
        }

        $virtualCard = VirtualCard::where([
            'card_id' => $id,
            'user_id' => $request->user()->id,
        ])->first();

        $virtualCard->is_block = true;
        $virtualCard->save();

        return response()->json([
            'message' => $response['message'],
        ], ResponseAlias::HTTP_OK);
    }

    public function unblockVirtualCard(Request $request, string $id)
    {
        $response = FlutterwaveService::unBlockVirtualCard($id);

        if ($response['status'] != 'success') {
            return response()->json([
                'message' => $response['message'],
            ], ResponseAlias::HTTP_NOT_FOUND);
        }

        $virtualCard = VirtualCard::where([
            'card_id' => $id,
            'user_id' => $request->user()->id,
        ])->first();

        $virtualCard->is_block = false;
        $virtualCard->save();

        return response()->json([
            'message' => $response['message'],
        ], ResponseAlias::HTTP_OK);
    }

    public function fundVirtualCard(Request $request)
    {
        if ($request->amount < 50) {
            return response()->json([
                'message' => 'Invalid amount entered',
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        if (! Hash::check($request->pin, $request->user()->transfer_pin)) {
            return response()->json([
                'message' => 'Invalid pin entered',
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        if (! $request->user()->hasCredits($request->amount)) {
            return response()->json([
                'message' => 'Insufficient account balance please refill and try again',
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $data = [
            'currency' => 'NGN',
            'amount' => $request->amount,
        ];

        $response = FlutterwaveService::fundVirtualCard($data, $request->card_id);

        if ($response['status'] != 'success') {
            return response()->json([
                'message' => $response['message'],
            ], ResponseAlias::HTTP_NOT_FOUND);
        }

        $request->user()->creditDeduct($request->amount, 'Fund virtual card');

        Transaction::create([
            'user_id' => $request->user()->id,
            'type' => 'debit',
            'references' => time(),
            'amount' => $request['amount'],
            'status' => 'success',
            'note' => 'Fund virtual card',
            'source_table' => 'virtual_cards',
        ]);

        return response()->json([
            'message' => 'Card funded successfully',
        ], ResponseAlias::HTTP_OK);
    }
}
