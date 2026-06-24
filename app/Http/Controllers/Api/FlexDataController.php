<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FlexDataList;
use App\Models\Transaction;
use App\Services\ClubConnectService;
use App\Services\OnesignalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class FlexDataController extends Controller
{
    public function listFlexDataPlans(Request $request)
    {
        $user = $request->user();
        $product = $request->input('product');
        $service = config("smeplug.airtimeProductArray.{$product}");

        $plans = FlexDataList::where('service_id', $product)
            ->where('status', 1)
            ->get();

        $transformedPlans = $plans->map(function (FlexDataList $plan) {
            return [
                'plan_id' => $plan->id,
                'id' => $plan->code,
                'service_id' => $plan->service_id,
                'name' => $plan->name,
                'amount' => $plan->price,
                'status' => $plan->status,
                'provider_image' => null,
            ];
        });

        return response()->json($transformedPlans, ResponseAlias::HTTP_OK);
    }

    public function purchaseFlexDataPlan(Request $request)
    {
        $user = $request->user();
        $inputs = $request->all();

        $amount = $inputs['amount'] ?? null;
        $phone = $inputs['phone_no'] ?? null;
        $productId = $inputs['product'] ?? null;
        $code = $inputs['code'] ?? null;
        $spl = $inputs['spl'] ?? 'no';

        $reference = 'MobileData_'.time();
        $inputs['references'] = $reference;

        if (! is_numeric($amount)) {
            return response()->json(['message' => 'Invalid amount entered'], ResponseAlias::HTTP_BAD_REQUEST);
        }

        if (! Hash::check($inputs['pin'] ?? '', $user->transfer_pin)) {
            return response()->json(['message' => 'Transaction pin is invalid'], ResponseAlias::HTTP_BAD_REQUEST);
        }

        if ((float) $amount < 0) {
            return response()->json(['message' => 'Invalid amount entered'], ResponseAlias::HTTP_BAD_REQUEST);
        }

        if ($spl === 'no' && ! $user->hasCredits($amount)) {
            return response()->json(['message' => 'Insufficient account balance please refill and try again'], ResponseAlias::HTTP_BAD_REQUEST);
        }

        if ($user->hasCredits($amount)) {

            $user->creditDeduct($amount);
            $network = config("smeplug.CKProductArray.{$productId}");

            $requestData = [
                'network' => $network,
                'code' => $code,
                'phone_no' => $phone,
                'references' => time(),
            ];

            try {
                $response = ClubConnectService::purchaseMobileDataPlans($requestData);

                if ($response['error']) {
                    return response()->json(['message' => $response['message']], ResponseAlias::HTTP_BAD_REQUEST);
                }

                $status = $response['status'] ?? '';

                if ($status === 'ORDER_RECEIVED' || $status === 'ORDER_PROCESSED' || $status === 'ORDER_COMPLETED') {

                    $desc = ($inputs['service'] ?? '').' '.($inputs['plan'] ?? '');

                    Transaction::create([
                        'user_id' => $user->id,
                        'type' => 'debit',
                        'references' => $reference,
                        'amount' => $amount,
                        'status' => 'success',
                        'note' => "Purchase {$desc} flex data plan",
                    ]);

                    try {
                        $notification = [
                            'contents' => "Hello {$user->firstname}, you have successfully activated the mobile flex data plan of {$desc}",
                            'title' => 'Activation of Flex Mobile Data Plan',
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
                    } catch (\Exception $ne) {
                        \Log::error('Flex Data Notification Error: '.$ne->getMessage());
                    }

                    $airtimeProvider = config("smeplug.airtimeProductArray.{$productId}");

                    return response()->json([
                        'references' => $reference,
                        'provider_id' => $airtimeProvider,
                        'amount' => $amount,
                        'phone_no' => $phone,
                        'details' => "Purchase {$desc} flex data plan",
                        'plan' => $inputs['plan'] ?? null,
                    ], 200);
                }

                $user->creditAdd($amount);

                return response()->json(['message' => 'We encountered some problems please try again'], ResponseAlias::HTTP_BAD_REQUEST);

            } catch (\Exception $e) {
                $user->creditAdd($amount);
                \Log::error('Flex Data Purchase Global Exception: '.$e->getMessage());

                return response()->json(['message' => 'We encountered some problems please try again'], ResponseAlias::HTTP_BAD_REQUEST);
            }
        }

        return response()->json(['message' => 'Fraud transaction detected'], ResponseAlias::HTTP_BAD_REQUEST);
    }
}
