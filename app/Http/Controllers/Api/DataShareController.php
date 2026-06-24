<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DataBank;
use App\Models\Transaction;
use App\Models\User;
use App\Services\ClubConnectService;
use App\Services\OnesignalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class DataShareController extends Controller
{
    public function dataBanks(Request $request)
    {
        $balances = DataBank::where('receiver_id', $request->user()->id)
            ->where('is_valid', true)
            ->whereIn('provider_id', [1, 2, 3, 4])
            ->groupBy('provider_id')
            ->selectRaw('provider_id, SUM(data_value) as total_data')
            ->pluck('total_data', 'provider_id');

        return response()->json([
            [
                'name' => 'MTN',
                'balance' => (string) ($balances->get(1) ?? 0),
                'image' => 'mtn.png',
            ],
            [
                'name' => 'Glo',
                'balance' => (string) ($balances->get(4) ?? 0),
                'image' => 'glo.png',
            ],
            [
                'name' => 'Airtel',
                'balance' => (string) ($balances->get(2) ?? 0),
                'image' => 'airtel.png',
            ],
            [
                'name' => '9Mobile',
                'balance' => (string) ($balances->get(3) ?? 0),
                'image' => '9mobile.png',
            ],
        ], ResponseAlias::HTTP_OK);
    }

    public function sentDataBanks(Request $request)
    {
        $shares = DataBank::where('sender_id', $request->user()->id)
            ->latest()
            ->take(100)
            ->get();

        return response()->json($shares, ResponseAlias::HTTP_OK);
    }

    public function receivedDataBanks(Request $request)
    {
        $shares = DataBank::where('receiver_id', $request->user()->id)
            ->latest()
            ->take(100)
            ->get();

        return response()->json($shares, ResponseAlias::HTTP_OK);
    }

    public function myDataBanks(Request $request, $provider)
    {
        $shares = DataBank::where('receiver_id', $request->user()->id)
            ->where('is_valid', true)
            ->where('provider_id', $provider)
            ->latest()
            ->take(100)
            ->get();

        return response()->json($shares, ResponseAlias::HTTP_OK);
    }

    public function verifyUserName(Request $request)
    {
        $username = $request->input('username');
        $user = User::where('email', $username)->first();

        if (! $user) {
            return response()->json([
                'message' => 'This beneficiary email does not exist please check and try again',
            ], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response()->json($user, ResponseAlias::HTTP_OK);
    }

    public function shareData(Request $request)
    {
        $user = $request->user();
        $inputs = $request->all();

        $planString = $inputs['plan'] ?? '';
        $price = $inputs['price'] ?? 0;
        $username = $inputs['username'] ?? null;
        $receiverId = $inputs['user_id'] ?? null;
        $productId = $inputs['product'] ?? null;
        $code = $inputs['code'] ?? null;
        $providerImage = $inputs['provider_image'] ?? null;

        $reference = 'DB_'.now()->timestamp;
        $inputs['references'] = $reference;

        $pattern = '/(\d+(?:\.\d+)?)\s*(MB|GB)/i';

        if (preg_match($pattern, $planString, $matches)) {
            $numericValue = (float) $matches[1];
            $unit = strtoupper($matches[2]);

            $gbValue = ($unit === 'MB') ? ($numericValue / 1024) : $numericValue;
            $finalDataValue = round($gbValue, 2);
        } else {
            return response()->json(['message' => 'Invalid data plan format provided'], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (! Hash::check($inputs['pin'] ?? '', $user->transfer_pin)) {
            return response()->json(['message' => 'Transaction pin is invalid'], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (! $user->hasCredits($price)) {
            return response()->json(['message' => 'Insufficient account balance please refill and try again'], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user->creditDeduct($price);

        if ($username !== $user->email) {
            $receiver = User::where('email', $username)
                ->first();

            if ($receiver) {
                $network = config("smeplug.airtimeProductArray.{$productId}", 'Data');
                $cleanDataLabel = $numericValue.$unit;

                $notification = [
                    'contents' => "Hello {$receiver->firstname}, you have received {$network} {$cleanDataLabel} sent to you by {$user->firstname} {$user->lastname}",
                    'title' => 'Data escrow received!',
                    'filters' => [
                        [
                            'field' => 'tag',
                            'key' => 'uid',
                            'relation' => '=',
                            'value' => $receiver->id,
                        ],
                    ],
                ];

                OnesignalService::sendPushNotification($notification);
            }
        }

        DataBank::create([
            'sender_id' => $user->id,
            'receiver_id' => $receiverId,
            'references' => $reference,
            'provider_id' => $productId,
            'plan' => $planString,
            'plan_code' => $code,
            'data_value' => $finalDataValue,
            'provider_image' => $providerImage,
            'price' => $price,
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'type' => 'debit',
            'references' => $reference,
            'amount' => $price,
            'status' => 'success',
            'note' => 'Send data of '.$planString.' to '.$username,
        ]);

        return response()->json([
            'message' => 'Data has been sent successfully!',
        ], ResponseAlias::HTTP_OK);
    }

    public function withdrawData(Request $request)
    {
        $user = $request->user();
        $id = $request->input('code');
        $phone = $request->input('phone');
        $pin = $request->input('pin');
        $data = DataBank::find($id);

        if (! $data) {
            return response()->json(['message' => 'Invalid plan selected'], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (! Hash::check($pin, $user->transfer_pin)) {
            return response()->json(['message' => 'Transaction pin is invalid'], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ((int) $data->receiver_id !== (int) $user->id) {
            return response()->json(['message' => 'Invalid plan selected'], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (! $data->is_valid) {
            return response()->json(['message' => 'This plan is no longer valid'], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }

        $pack = [
            'product' => $data->provider_id,
            'code' => $data->plan_code,
            'phone_no' => $phone,
            'references' => time(),
        ];

        $response = ClubConnectService::purchaseMobileDataPlans($pack);

        if ($response['error']) {
            return response()->json(['message' => $response['message']], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }

        $status = $response['status'] ?? '';
        $statusCode = (int) ($response['statuscode'] ?? 0);

        if ($status === 'INVALID_API_ERROR_1' || $status === 'INVALID_API_ERROR_2' || ($statusCode > 0 && $statusCode < 400)) {

            $data->update([
                'is_valid' => false,
            ]);

            return response()->json([
                'message' => 'Data has been withdrawn successfully!',
            ], ResponseAlias::HTTP_OK);
        }

        return response()->json([
            'message' => 'We are running a quick maintenance we will be right back',
        ], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function shareDataAgent(Request $request)
    {
        // TODO: Implement shareDataAgent
        return response()->json(['message' => 'Method not implemented'], ResponseAlias::HTTP_NOT_IMPLEMENTED);
    }
}
