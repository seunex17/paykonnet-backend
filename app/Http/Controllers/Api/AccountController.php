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
use App\Models\SubAgent;
use App\Models\Transaction;
use App\Models\User;
use App\Models\VirtualCard;
use App\Models\Wallet;
use App\Services\FidelityService;
use App\Services\FlutterwaveService;
use App\Services\MonoService;
use App\Services\OnesignalService;
use App\Services\QoreIdService;
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

    public function withdrawVirtualCard(Request $request)
    {
        $user = $request->user();

        $validate = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1',
            'pin' => 'required',
            'card_id' => 'required',
        ]);

        if ($validate->fails()) {
            \Log::info('Validation failed', ['errors' => $validate->errors()]);
            return response()->json([
                'message' => $validate->errors()->first(),
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        if (! Hash::check($request->pin, $user->transfer_pin)) {
            \Log::info('Pin check failed', ['pin' => $request->pin, 'hashed' => $user->transfer_pin]);
            return response()->json(['message' => 'Transaction pin is invalid'], ResponseAlias::HTTP_BAD_REQUEST);
        }

        try {
            $response = FlutterwaveService::withdrawVirtualCard([
                'amount' => $request->amount,
            ], $request->card_id);

            \Log::info('Flutterwave withdrawal response', ['res' => $response]);

            if (isset($response['status']) && $response['status'] === 'success') {

                $user->creditAdd($request->amount, 'Withdraw from virtual card');

                Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'credit',
                    'references' => now()->timestamp,
                    'amount' => $request->amount,
                    'status' => 'success',
                    'note' => 'Withdraw from virtual card',
                ]);

                return response()->json([
                    'message' => 'Fund withdrawal successfully',
                ], ResponseAlias::HTTP_OK);
            }

            return response()->json([
                'message' => $response->message ?? 'Provider transaction failed',
            ], ResponseAlias::HTTP_BAD_REQUEST);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'We encountered some problems, please try again',
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function fundWallet(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ref' => 'required',
            'amount' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $amount = $request->amount / 100;

        $request->user()->creditAdd($request->amount, 'Fund wallet');

        // 3. Prepare and send OneSignal Push Notification
        $notification = [
            'contents' => "Hello {$request->user()->firstname}, your Paykonet wallet has been credited with N{$amount}. This amount is now available for spending.",
            'title' => 'Your Account has been credited successfully!',
            'filters' => [
                [
                    'field' => 'tag',
                    'key' => 'uid',
                    'relation' => '=',
                    'value' => $request->user()->id,
                ],
            ],
        ];

        OnesignalService::sendPushNotification($notification);

        Transaction::create([
            'user_id' => $request->user()->id,
            'type' => 'credit',
            'references' => 'Wallet-'.now()->timestamp,
            'amount' => $amount,
            'status' => 'success',
            'note' => 'Wallet funding',
        ]);

        return response()->json([
            'message' => 'Your wallet has been topped-up with '.$amount,
        ], ResponseAlias::HTTP_OK);
    }

    public function idVerification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|string',
            'number' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $idExists = User::where('id_type', $request->type)
            ->where('id_number', $request->number)
            ->exists();

        if ($idExists) {
            return response()->json([
                'message' => 'This id has already been used for verification',
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        try {
            $response = QoreIdService::identification($request->user(), $request->type, $request->number);

            if (is_numeric($response['status']) && $response['status'] > 200) {
                return response()->json([
                    'message' => $response->message ?? 'Verification provider error',
                ], ResponseAlias::HTTP_BAD_REQUEST);
            }

            if (isset($response->status['status']) && $response->status['status'] == 'id_mismatch') {
                return response()->json([
                    'message' => 'The provided id does not match the name of this account.',
                ], ResponseAlias::HTTP_BAD_REQUEST);
            }
            $user = $request->user();

            $updated = $user->update([
                'id_type' => $request->type,
                'id_number' => $request->number,
                'id_verified' => true,
            ]);

            if (! $updated) {
                return response()->json([
                    'message' => 'Something went wrong please try again later',
                ], ResponseAlias::HTTP_BAD_REQUEST);
            }

            return response()->json([
                'message' => 'ID verification successful!',
            ], ResponseAlias::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Verification unavailable',
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        if ($user->id_verified) {
            return response()->json([
                'message' => 'This account has already been verified',
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $validator = Validator::make($request->all(), [
            'firstname' => 'sometimes|required|string|max:255',
            'lastname' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,'.$user->id,
            'phone' => 'sometimes|required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $allowedFields = $request->only(['firstname', 'lastname', 'email', 'phone']);

        $updated = $user->update($allowedFields);

        if (! $updated) {
            return response()->json([
                'message' => 'Something went wrong, please try again later',
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'message' => 'Your account has been updated successfully!',
        ], ResponseAlias::HTTP_OK);
    }

    public function upgradeAgent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'level' => 'required|in:1,2,3',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $user = $request->user();
        $agentType = $request->level;

        $cost = match ($agentType) {
            '1' => 100000,
            '2' => 10000,
            default => 5000,
        };

        if (! $user->hasCredits($cost)) {
            return response()->json([
                'message' => 'Insufficient account balance please refill and try again',
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $user->creditDeduct($cost, 'Agent upgrade');

        $nextPaymentDate = now()->addYear();

        $user->update([
            'agent_level' => $agentType,
            'next_agent_payment_date' => $nextPaymentDate,
        ]);

        // 6. Record the transaction using Eloquent
        Transaction::create([
            'user_id' => $user->id,
            'type' => 'debit',
            'references' => 'agent-'.time(),
            'amount' => $cost,
            'status' => 'success',
            'note' => 'Upgrade to agent',
        ]);

        return response()->json([
            'message' => 'Upgrade to agent successful!',
        ], ResponseAlias::HTTP_OK);
    }

    public function cancelAgentSubscription(Request $request)
    {
        $user = $request->user();

        $user->update([
            'agent_level' => 0,
            'next_agent_payment_date' => null,
        ]);

        return response()->json([
            'message' => 'Subscription has been canceled',
        ], ResponseAlias::HTTP_OK);
    }

    public function addSubAgent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'agent_level' => 'required|in:2,3',
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $user = $request->user();
        $agentType = $request->agent_level;

        if ($user->email === $request->email) {
            return response()->json([
                'message' => 'This seems to be your account',
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $cost = match ($agentType) {
            '2' => 1000,
            default => 500,
        };

        if (! $user->hasCredits($cost)) {
            return response()->json([
                'message' => 'Insufficient account balance please refill and try again',
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $agentUser = User::where('email', $request->email)->first();

        if (! $agentUser) {
            return response()->json([
                'message' => 'This user does not exist',
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $subAgentExists = SubAgent::where('agent_id', $agentUser->id)->exists();

        if ($subAgentExists) {
            return response()->json([
                'message' => 'This agent already exists',
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $user->creditDeduct($cost, 'Create sub agent');

        SubAgent::create([
            'agent_level' => $agentType,
            'user_id' => $user->id,
            'agent_id' => $agentUser->id,
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'type' => 'debit',
            'references' => 'subagent-'.time(),
            'amount' => $cost,
            'status' => 'success',
            'note' => 'Added sub agent',
        ]);

        return response()->json([
            'message' => 'Sub agent has been added successfully',
        ], ResponseAlias::HTTP_OK);
    }

    public function connectMonoAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        try {
            $response = MonoService::authenticate($request->code);

            if (isset($response['status']) && $response['status'] === 'successful') {
                $monoID = $response['data']['id'];

                $user = $request->user();
                $user->update([
                    'mono_id' => $monoID,
                ]);

                return response()->json([
                    'message' => 'Account connected successfully',
                ], ResponseAlias::HTTP_OK);
            }

            return response()->json([
                'message' => $response->message ?? 'Unable to authenticate with Mono',
            ], ResponseAlias::HTTP_BAD_REQUEST);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong, please try again',
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function createMonoMandate(Request $request)
    {
        $amount = 5000000;
        $loanDuration = 30;

        $data = [
            'amount' => (int) number_format($amount, 2, '', ''),
            'type' => 'recurring-debit',
            'method' => 'mandate',
            'mandate_type' => 'emandate',
            'debit_type' => 'variable',
            'description' => 'Paykonnet loan repayment',
            'reference' => 'Pakonnet'.now()->timestamp,
            'redirect_url' => url('/'),
            'customer' => [
                'id' => '6749cb97e215dd6a14e96213',
            ],
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays($loanDuration)->toDateString(),
        ];

        try {
            $response = MonoService::createMandate($data);

            if (isset($response['status']) && $response['status'] === 'successful') {
                return response()->json($response['data'], ResponseAlias::HTTP_OK);
            }

            return response()->json([
                'message' => $response->message ?? 'Unable to create mandate with Mono',
            ], ResponseAlias::HTTP_BAD_REQUEST);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong while setting up your mandate. Please try again.',
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function repayOweSpl(Request $request)
    {
        $user = $request->user();

        $oweAmount = SellPayLater::where('user_id', $user->id)
            ->where('paid', false)
            ->sum('amount');

        if ($oweAmount <= 0) {
            return response()->json([
                'message' => 'You do not have any active outstanding balances to pay.',
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        if (! $user->hasCredits($oweAmount)) {
            return response()->json([
                'message' => 'You dont have enough bal to complete this request',
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $user->creditDeduct($oweAmount, 'Paykonnet loan repayment');

        SellPayLater::where('user_id', $user->id)
            ->where('paid', false)
            ->update(['paid' => true]);

        return response()->json([
            'message' => 'Transaction successful',
        ], ResponseAlias::HTTP_OK);
    }
}
