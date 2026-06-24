<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AirtimeTopup;
use App\Models\BettingWalletTopup;
use App\Models\CableSubscription;
use App\Models\CashLoan;
use App\Models\DataLoan;
use App\Models\DataLoanList;
use App\Models\ElectricBillService;
use App\Models\ElectricityBill;
use App\Models\MobileDataTopup;
use App\Models\SellPayLater;
use App\Models\SubAgent;
use App\Models\Transaction;
use App\Services\ClubConnectService;
use App\Services\OnesignalService;
use App\Services\QoreIdService;
use App\Services\UtilityService;
use App\Services\VTPassService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class BillsController extends Controller
{
    public function getRecentAirtimeTopup(Request $request)
    {
        try {
            $topups = AirtimeTopup::where('user_id', $request->user()->id)
                ->orderBy('id', 'desc')
                ->limit(50)
                ->get();

            $transactions = [];
            $providerArray = config('smeplug.airtimeProductArray', []);

            foreach ($topups as $topup) {
                $transactions[] = [
                    'provider' => $providerArray[$topup->provider_id] ?? 'Unknown Provider',
                    'amount' => number_format($topup->amount, 0),
                ];
            }

            return response()->json($transactions, ResponseAlias::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'We encountered some problems please try again later',
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function fetchElectricServiceCode(Request $request)
    {
        $services = ElectricBillService::all();

        return response()->json([
            'services' => $services,
        ], ResponseAlias::HTTP_OK);
    }

    public function fetchUnpaidDataLoan(Request $request)
    {
        $dataLoan = DataLoan::with('debitCard')
            ->where('user_id', $request->user()->id)
            ->where('fully_paid', false)
            ->first();

        return response()->json($dataLoan ?? [], ResponseAlias::HTTP_OK);
    }

    public function fetchSingleDataLoan(Request $request, $id)
    {
        $loan = DataLoan::find($id);

        if (! $loan || $loan->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Loan record not found',
            ], ResponseAlias::HTTP_NOT_FOUND);
        }

        return response()->json([
            'loan' => $loan,
        ], ResponseAlias::HTTP_OK);
    }

    public function fetchDataLoanList(Request $request)
    {
        $product = $request->query('product');

        if (! $product) {
            return response()->json([], ResponseAlias::HTTP_NOT_FOUND);
        }

        $dataLoansPlan = DataLoanList::where('service_id', $product)->get();

        return response()->json($dataLoansPlan, ResponseAlias::HTTP_OK);
    }

    public function fetchDataLoanLength(Request $request)
    {
        $loanDuration = 30;

        return response()->json([
            'length' => $loanDuration.' days',
        ], ResponseAlias::HTTP_OK);
    }

    public function allDataLoan(Request $request)
    {
        $dataLoans = DataLoan::where('user_id', $request->user()->id)
            ->latest()
            ->take(100)
            ->get();

        return response()->json($dataLoans, ResponseAlias::HTTP_OK);
    }

    public function loadBettingCompanies(Request $request)
    {
        $data = [];

        $response = ClubConnectService::loadBettingCompanies();
        $plans = $response['BETTING_COMPANY'] ?? null;

        if ($plans) {
            foreach ($plans as $plan) {
                $data[] = [
                    'name' => ucwords(str_replace('-', ' ', $plan['PRODUCT_CODE'])),
                    'code' => $plan['PRODUCT_CODE'],
                ];
            }
        }

        return response()->json($data, ResponseAlias::HTTP_OK);
    }

    public function fetchCashLoanLength()
    {
        return response()->json([
            'length' => '30 days',
        ], ResponseAlias::HTTP_OK);
    }

    public function fetchUnpaidCashLoan(Request $request)
    {
        $cashLoan = CashLoan::with('debitCard')
            ->where('user_id', $request->user()->id)
            ->where('fully_paid', false)
            ->first();

        return response()->json($cashLoan ?? [], ResponseAlias::HTTP_OK);
    }

    public function allCashLoan(Request $request)
    {
        $cashLoans = CashLoan::where('user_id', $request->user()->id)
            ->latest()
            ->take(100)
            ->get();

        return response()->json($cashLoans, ResponseAlias::HTTP_OK);
    }

    public function listJambService(Request $request)
    {
        $response = VTPassService::getJambVariationCode();

        return response()->json($response, ResponseAlias::HTTP_OK);
    }

    public function listMobileDataPlans(Request $request)
    {
        $user = $request->user();
        $product = $request->input('product');
        $service = config("smeplug.airtimeProductArray.{$product}");
        $data = [];

        $response = ClubConnectService::listMobileDataPlans();

        if ($response['error']) {
            return response()->json($response['message'], ResponseAlias::HTTP_NOT_FOUND);
        }

        $plans = $response['MOBILE_NETWORK'][$service][0]['PRODUCT'] ?? null;

        if ($plans) {
            foreach ($plans as $plan) {
                $data[] = [
                    'id' => $plan['PRODUCT_ID'],
                    'name' => $plan['PRODUCT_NAME'],
                    'price' => number_format($plan['PRODUCT_AMOUNT'], 0, '', ''),
                ];
            }
        }

        return response()->json($data, ResponseAlias::HTTP_OK);
    }

    public function purchaseMobileDataPlan(Request $request)
    {
        $user = $request->user();
        $inputs = $request->all();
        $reference = 'MobileData_'.time();
        $inputs['references'] = $reference;

        $originalAmount = $inputs['amount'] ?? null;
        $phone = $inputs['phone_no'] ?? null;
        $pin = $inputs['pin'] ?? null;
        $spl = $inputs['spl'] ?? 'no';
        $productId = $inputs['product'] ?? null;
        $plan = $inputs['plan'] ?? null;

        if (! is_numeric($originalAmount)) {
            return response()->json(['message' => 'Invalid amount entered'], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $amount = (float) $originalAmount;
        if ((int) $user->agent_level !== 0) {
            $amount -= UtilityService::agentPercent($user, $amount);
        } else {
            $isSubAgent = SubAgent::where('agent_id', $user->id)->exists();
            if ($isSubAgent) {
                $amount -= UtilityService::agentPercent($user, $amount);
            }
        }

        if (! Hash::check($pin, $user->transfer_pin)) {
            return response()->json(['message' => 'Transaction pin is invalid'], ResponseAlias::HTTP_BAD_REQUEST);
        }

        if ($amount < 0) {
            return response()->json(['message' => 'Invalid amount entered'], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $currentBalance = (float) $user->creditBalance();

        if ($spl === 'no' && $currentBalance < $amount) {
            return response()->json(['message' => 'Insufficient account balance please refill and try again'], ResponseAlias::HTTP_BAD_REQUEST);
        }

        if ($spl === 'yes') {
            if ($currentBalance < 25000) {
                return response()->json(['message' => 'Your bal must be at least 25000 to use this feature'], ResponseAlias::HTTP_BAD_REQUEST);
            }
        }

        if ($spl === 'no') {
            $user->creditDeduct($amount);
        }

        $postDebitBalance = (float) $user->creditBalance();

        if ($spl === 'yes' || $postDebitBalance >= 0) {
            $response = ClubConnectService::purchaseMobileDataPlans($inputs);

            if ($response['error']) {
                if ($request->spl == 'no') {
                    $user->creditAdd($amount);
                }

                return response()->json($response['message'], ResponseAlias::HTTP_OK);
            }

            $orderStatus = $response['status'] ?? '';

            if (in_array($orderStatus, ['ORDER_RECEIVED', 'ORDER_PROCESSED', 'ORDER_COMPLETED'])) {
                $mobileData = MobileDataTopup::create([
                    'user_id' => $user->id,
                    'references' => $reference,
                    'provider_id' => $productId,
                    'amount' => $amount,
                    'phone_no' => $phone,
                    'details' => $orderStatus,
                    'plan' => $plan,
                ]);

                if ($spl === 'yes') {
                    SellPayLater::create([
                        'user_id' => $user->id,
                        'product' => $plan,
                        'amount' => $amount,
                    ]);
                }

                Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'debit',
                    'references' => $reference,
                    'amount' => $amount,
                    'status' => 'success',
                    'note' => 'Purchase '.$orderStatus,
                ]);

                return response()->json($mobileData, ResponseAlias::HTTP_OK);
            }

            if ($spl === 'no') {
                $user->creditAdd($amount);
            }

            return response()->json(['message' => 'We encountered some problems please try again'], 422);
        }

        return response()->json(['message' => 'Fraud transaction detected'], 422);
    }

    public function listCablePlans(Request $request)
    {
        $response = VTPassService::getCableTvVariationCode($request->all());

        if ($response['error']) {
            return response()->json($response['message'], ResponseAlias::HTTP_BAD_REQUEST);
        }

        return response()->json($response, ResponseAlias::HTTP_OK);
    }

    public function verifySmartCardNumber(Request $request)
    {
        $response = VTPassService::verifySmartCardNumber($request->all());

        if ($response['error']) {
            return response()->json($response['message'], ResponseAlias::HTTP_BAD_REQUEST);
        }

        if (! empty($response['content']['WrongBillersCode'])) {
            $errorMessage = $response['content']['error'] ?? 'Invalid smart card or biller details';

            return response()->json(['message' => $errorMessage], ResponseAlias::HTTP_BAD_REQUEST);
        }

        return response()->json($response, ResponseAlias::HTTP_OK);
    }

    public function purchaseCableSubscription(Request $request)
    {
        $user = $request->user();
        $inputs = $request->all();
        $reference = 'Cable_'.now()->timestamp;
        $inputs['references'] = $reference;

        $amount = $inputs['price'] ?? null;
        $smartCardNumber = $inputs['smart_card_no'] ?? null;
        $pin = $inputs['pin'] ?? null;
        $service = $inputs['service'] ?? null;
        $productId = $inputs['product'] ?? null;
        $phone = $inputs['phone_no'] ?? null;
        $plan = $inputs['plan'] ?? null;

        if (! is_numeric($amount)) {
            return response()->json(['message' => 'Invalid amount entered'], ResponseAlias::HTTP_BAD_REQUEST);
        }

        if (! Hash::check($pin, $user->transfer_pin)) {
            return response()->json(['message' => 'Transaction pin is invalid'], ResponseAlias::HTTP_BAD_REQUEST);
        }

        if ((float) $amount < 0) {
            return response()->json(['message' => 'Invalid amount entered'], ResponseAlias::HTTP_BAD_REQUEST);
        }

        if (! $user->hasCredits($amount)) {
            return response()->json(['message' => 'Insufficient account balance please refill and try again'], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $user->creditDeduct($amount);

        if ((float) $user->creditBalance() >= 0) {
            $response = VTPassService::purchaseCableBill($inputs);

            if ($response['error']) {
                $user->creditAdd($amount);

                return response()->json($response['message'], ResponseAlias::HTTP_BAD_REQUEST);
            }

            if (($response['code'] ?? '') !== '000') {
                $user->creditAdd($amount);

                return response()->json(['message' => 'We are having problems processing this request please try again later.'], ResponseAlias::HTTP_BAD_REQUEST);
            }

            if (($response['response_description'] ?? '') !== 'TRANSACTION SUCCESSFUL') {
                $user->creditAdd($amount);

                return response()->json(['message' => $response['response_description']], ResponseAlias::HTTP_BAD_REQUEST);
            }

            $transactionType = $response['content']['transactions']['type'] ?? 'Cable TV';

            $cable = CableSubscription::create([
                'user_id' => $user->id,
                'references' => $reference,
                'provider_id' => $productId,
                'amount' => $amount,
                'phone_no' => $phone,
                'smart_card_no' => $smartCardNumber,
                'details' => "{$transactionType} ($service)",
                'product' => $service,
                'package' => $plan,
            ]);

            Transaction::create([
                'user_id' => $user->id,
                'type' => 'debit',
                'references' => $reference,
                'amount' => $amount,
                'status' => 'success',
                'note' => "Purchase {$transactionType} {$service}",
            ]);

            return response()->json($cable, 200);
        }

        return response()->json(['message' => 'Fraud transaction detected.'], ResponseAlias::HTTP_BAD_REQUEST);
    }

    public function verifyElectricityBMeterNumber(Request $request)
    {
        $inputs = $request->all();
        $inputs['type'] = $inputs['variation_code'] ?? null;

        $response = VTPassService::verifyElectricityMeterNumber($inputs);

        if ($response['error']) {
            return response()->json($response['message'], ResponseAlias::HTTP_BAD_REQUEST);
        }

        if (isset($response['content']['error'])) {
            return response()->json([
                'message' => $response['content']['error'],
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        return response()->json($response, ResponseAlias::HTTP_OK);
    }

    public function purchaseElectricityBill(Request $request)
    {
        $user = $request->user();
        $inputs = $request->all();
        $reference = 'Electricity_'.now()->timestamp;
        $inputs['request_id'] = $reference;

        $amount = $inputs['amount'] ?? null;
        $serviceId = $inputs['serviceID'] ?? null;
        $pin = $inputs['pin'] ?? null;
        $phone = $inputs['phone'] ?? null;
        $meterNo = $inputs['billersCode'] ?? null;

        if (! is_numeric($amount)) {
            return response()->json(['message' => 'Invalid amount entered'], ResponseAlias::HTTP_BAD_REQUEST);
        }

        if (! Hash::check($pin, $user->transfer_pin)) {
            return response()->json(['message' => 'Transaction pin is invalid'], ResponseAlias::HTTP_BAD_REQUEST);
        }

        if ((float) $amount < 0) {
            return response()->json(['message' => 'Invalid amount entered'], ResponseAlias::HTTP_BAD_REQUEST);
        }

        if ((float) $amount < 500) {
            return response()->json(['message' => 'Recharge amount must be at least N500'], ResponseAlias::HTTP_BAD_REQUEST);
        }

        if (! $user->hasCredits($amount)) {
            return response()->json(['message' => 'Insufficient account balance please refill and try again'], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $provider = ElectricBillService::where('code', $serviceId)->first();
        $providerId = $provider?->id;

        $user->creditDeduct($amount);

        if ($user->creditBalance() >= 0) {
            $response = VTPassService::purchaseElectricity($inputs);

            if (isset($response['content']['transactions'])) {
                if (($response['response_description'] ?? '') === 'TRANSACTION SUCCESSFUL') {

                    $electricData = ElectricityBill::create([
                        'user_id' => $user->id,
                        'references' => $reference,
                        'provider_id' => $providerId,
                        'amount' => $amount,
                        'phone_no' => $phone,
                        'meter_no' => $meterNo,
                        'details' => $response['content']['transactions']['product_name'] ?? 'Electricity Payment',
                        'token' => $response['purchased_code'] ?? null,
                    ]);

                    $purchasedToken = $response['purchased_code'] ?? 'N/A';
                    Transaction::create([
                        'user_id' => $user->id,
                        'type' => 'debit',
                        'references' => $reference,
                        'amount' => $amount,
                        'status' => 'success',
                        'note' => 'Purchase electricity with token: '.$purchasedToken,
                    ]);

                    return response()->json($electricData, ResponseAlias::HTTP_OK);
                }

                $user->creditAdd($amount);

                return response()->json(['message' => $response['response_description']], ResponseAlias::HTTP_OK);
            }

            $user->creditAdd($amount);

            return response()->json(['message' => 'We encountered some problem please try again later'], ResponseAlias::HTTP_BAD_REQUEST);
        }

        return response()->json(['message' => 'Fraud transaction detected!'], ResponseAlias::HTTP_BAD_REQUEST);
    }

    public function createNewDataLoan(Request $request)
    {
        $user = $request->user();
        $inputs = $request->all();
        $ref = 'MobileData_'.now()->timestamp;

        $deviceId = $inputs['device_id'] ?? null;
        $product = $inputs['product'] ?? null;
        $code = $inputs['code'] ?? null;
        $phone = $inputs['phone'] ?? null;
        $plan = $inputs['plan'] ?? null;
        $price = (float) ($inputs['price'] ?? 0);
        $debitCardId = $inputs['debit_card_id'] ?? null;

        $planData = [
            'references' => $ref,
            'product' => $product,
            'code' => $code,
            'phone_no' => $phone,
            'customer_reference' => $ref,
        ];

        $response = ClubConnectService::purchaseMobileDataPlans($planData);

        if ($response['error']) {
            return response()->json($response['message'], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $status = $response['status'] ?? '';
        $statusCode = (int) ($response['statuscode'] ?? 0);

        if ($status === 'INVALID_API_ERROR_1' || $status === 'INVALID_API_ERROR_2' || ($statusCode > 0 && $statusCode < 400)) {
            $loanDurationDays = config('site.loanDuration.data', 14);

            $dataLoan = DataLoan::create([
                'uuid' => Str::uuid()->toString(),
                'product' => $product,
                'phone' => $phone,
                'plan' => $plan,
                'code' => $code,
                'guarantor_email' => $inputs['guarantorEmail'] ?? null,
                'guarantor_phone_number' => $inputs['guarantorPhoneNumber'] ?? null,
                'device_id' => $deviceId,
                'debit_card_id' => $debitCardId,
                'amount' => $price,
                'repayment_amount' => $price,
                'amount_paid' => 0,
                'due_date' => now()->addDays($loanDurationDays),
                'user_id' => $user->id,
                'product_image' => $inputs['product_image'] ?? null,
            ]);

            $percentage = config('site.loanEarnPercentage', 10); // Fallback to 10%
            $bonus = ($price * $percentage) / 100;

            if ($bonus >= 50) {

                ClubConnectService::purchaseMobileAirtime([
                    'product' => $product,
                    'phone_no' => $phone,
                    'amount' => $bonus,
                    'references' => 'LoanBonus-'.now()->timestamp,
                ]);

                return response()->json([
                    'message' => 'Data loan received successful Plus a 10% worth of airtime of the amount you borrowed.',
                ], ResponseAlias::HTTP_OK);
            }

            return response()->json([
                'message' => 'Data loan has been processed successfully',
            ], ResponseAlias::HTTP_OK);
        }

        return response()->json(['message' => 'We can not process this transaction please try again.'], ResponseAlias::HTTP_BAD_REQUEST);
    }

    public function checkDataLoanEligible(Request $request)
    {
        $user = $request->user();
        $deviceId = $request->input('device_id');
        $guarantorPhone = $request->input('guarantorPhoneNumber');
        $loanPhone = $request->input('phone');

        if ((int) $user->agent_level === 1) {
            return response()->json([
                'status' => 'ok',
            ], ResponseAlias::HTTP_OK);
        }

        $hasUnpaidLoan = DataLoan::where('user_id', $user->id)
            ->where('fully_paid', false)
            ->exists();

        if ($hasUnpaidLoan) {
            return response()->json([
                'message' => 'We detected that you have an unpaid loan',
            ], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }

        $isDeviceLocked = DataLoan::where('device_id', $deviceId)
            ->where('fully_paid', false)
            ->exists();

        if ($isDeviceLocked) {
            return response()->json([
                'message' => 'You are not eligible for this loan',
            ], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($guarantorPhone === $loanPhone) {
            return response()->json([
                'message' => 'Guarantor phone number can not be the same as loan phone number.',
            ], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response()->json([
            'status' => 'ok',
        ], ResponseAlias::HTTP_OK);
    }

    public function verifyBettingCustomer(Request $request)
    {
        $response = ClubConnectService::verifyBettingCustomerId($request->all());

        if ($response['error']) {
            return response()->json($response['message'], ResponseAlias::HTTP_BAD_REQUEST);
        }

        if (($response['status'] ?? '') === '00') {
            return response()->json($response, ResponseAlias::HTTP_OK);
        }

        $errorMessage = $response['customer_name'] ?? 'Invalid customer ID or platform details';

        return response()->json(['message' => $errorMessage], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function fundBettingWallet(Request $request)
    {
        $user = $request->user();
        $inputs = $request->all();
        $reference = 'Betting_'.time();
        $inputs['references'] = $reference;

        $amount = $inputs['amount'] ?? null;
        $company = $inputs['company'] ?? null;
        $customerId = $inputs['customer_id'] ?? null;
        $pin = $inputs['pin'] ?? null;

        if (! is_numeric($amount)) {
            return response()->json(['message' => 'Invalid amount entered'], ResponseAlias::HTTP_BAD_REQUEST);
        }

        if ((float) $amount < 100) {
            return response()->json(['message' => 'Minimum amount is N100'], ResponseAlias::HTTP_BAD_REQUEST);
        }

        if (! Hash::check($pin, $user->transfer_pin)) {
            return response()->json(['message' => 'Transaction pin is invalid'], ResponseAlias::HTTP_BAD_REQUEST);
        }

        if (! $user->hasCredits($amount)) {
            return response()->json(['message' => 'Insufficient account balance please refill and try again'], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $user->creditDeduct($amount);

        if ($user->creditBalance() >= 0) {
            try {
                $response = ClubConnectService::fundBettingWallet($inputs);

                if ($response['error']) {
                    return response()->json($response['message'], ResponseAlias::HTTP_BAD_REQUEST);
                }

                if (($response['status'] ?? '') === 'ORDER_RECEIVED') {
                    try {
                        $notification = [
                            'contents' => "Hello {$user->firstname}, your {$company} has been funded with {$amount}",
                            'title' => 'Your Betting Wallet Has Been Refilled.',
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
                        Log::error('Betting Funding Notification Error: '.$ne->getMessage());
                    }

                    $bettingTxn = BettingWalletTopup::create([
                        'user_id' => $user->id,
                        'references' => $reference,
                        'company' => $company,
                        'amount' => $amount,
                        'customer_id' => $customerId,
                    ]);

                    Transaction::create([
                        'user_id' => $user->id,
                        'type' => 'debit',
                        'references' => $reference,
                        'amount' => $amount,
                        'status' => 'success',
                        'note' => "Fund {$company} betting wallet with {$amount}",
                    ]);

                    return response()->json($bettingTxn, 200);
                }

                $user->creditAdd($amount);

                return response()->json(['message' => 'We are currently running some maintenance please try again later.'], ResponseAlias::HTTP_OK);

            } catch (\Exception $e) {
                $user->creditAdd($amount);

                return response()->json(['message' => 'We encountered some problem please try again later.'], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        return response()->json(['message' => 'Fraud transaction detected!'], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function checkCashLoanEligible(Request $request)
    {
        $user = $request->user();
        $deviceId = $request->input('device_id');
        $amount = (float) $request->input('amount', 0);
        $nin = $request->input('nin');

        $hasUnpaidLoan = CashLoan::where('user_id', $user->id)
            ->where('fully_paid', false)
            ->exists();

        if ($hasUnpaidLoan) {
            return response()->json(['message' => 'We detected that you are having an unpaid loan'], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }

        $isDeviceLocked = CashLoan::where('device_id', $deviceId)
            ->where('fully_paid', false)
            ->exists();

        if ($isDeviceLocked) {
            return response()->json(['message' => 'You are not eligible for this loan'], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($amount < 1000) {
            return response()->json(['message' => 'Invalid amount requested!'], ResponseAlias::HTTP_BAD_REQUEST);
        }

        if ($amount > 10000) {
            return response()->json(['message' => 'We can not give above 10,000 at the moment'], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }

        $totalSpent = Transaction::where('user_id', $user->id)
            ->where('type', 'debit')
            ->sum('amount');

        if ($totalSpent < 50000) {
            return response()->json(['message' => 'Your account state can not get a loan at the moment.'], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }

        // 6. QoreID Identification Verification Pipeline
        try {
            $response = QoreIdService::identification($user, 'nin', $nin);
            $status = $response['status'] ?? null;

            if (is_numeric($status) && (int) $status > 200) {
                $errorMessage = $response['message'] ?? 'Identification verification rejected';

                return response()->json(['message' => $errorMessage], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
            }

            if (is_object($status) && isset($status['status']) && $status['status'] === 'id_mismatch') {
                return response()->json(['message' => 'This provided id does not Match the name of this account.'], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
            }

            return response()->json([
                'status' => 'ok',
            ], ResponseAlias::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Verification unavailable'], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function createNewCashLoan(Request $request)
    {
        $user = $request->user();
        $inputs = $request->all();
        $amount = (float) ($inputs['amount'] ?? 0);
        $debitCardId = $inputs['debit_card_id'] ?? null;
        $ref = 'CashLoan_'.time();

        $user->creditAdd($amount);
        $loanDurationDays = 30;

        $cashLoan = CashLoan::create([
            'uuid' => (string) Str::uuid(),
            'nin' => $inputs['nin'] ?? null,
            'guarantor_email' => $inputs['guarantorEmail'] ?? null,
            'guarantor_phone_number' => $inputs['guarantorPhoneNumber'] ?? null,
            'device_id' => $inputs['device_id'] ?? null,
            'debit_card_id' => $debitCardId,
            'amount' => $amount,
            'repayment_amount' => $amount,
            'amount_paid' => 0,
            'due_date' => now()->addDays($loanDurationDays),
            'user_id' => $user->id,
        ]);

        return response()->json([
            'message' => 'Cash loan has been processed successfully!',
        ], ResponseAlias::HTTP_OK);
    }

    public function verifyJambProfileId(Request $request)
    {
        // TODO: Implement verifyJambProfileId
        return response()->json(['message' => 'Method not implemented'], ResponseAlias::HTTP_NOT_IMPLEMENTED);
    }

    public function purchaseJambPin(Request $request)
    {
        // TODO: Implement purchaseJambPin
        return response()->json(['message' => 'Method not implemented'], ResponseAlias::HTTP_NOT_IMPLEMENTED);
    }

    public function purchaseAirtime(Request $request)
    {
        $user = $request->user();
        $inputs = $request->all();
        $reference = 'Airtime_'.now()->timestamp;
        $inputs['references'] = $reference;

        $amount = $inputs['amount'] ?? null;
        $phone = $inputs['phone_no'] ?? null;
        $pin = $inputs['pin'] ?? null;
        $productId = $inputs['product'] ?? null;

        if (! is_numeric($amount)) {
            return response()->json(['message' => 'Invalid amount entered'], ResponseAlias::HTTP_BAD_REQUEST);
        }

        if (! Hash::check($pin, $user->transfer_pin)) {
            return response()->json(['message' => 'Transaction pin is invalid'], ResponseAlias::HTTP_BAD_REQUEST);
        }

        if (! $user->hasCredits($amount)) {
            return response()->json(['message' => 'Insufficient account balance please refill and try again'], ResponseAlias::HTTP_BAD_REQUEST);
        }

        // 4. Hit the ClubConnect Vendor API Endpoint wrapper
        $response = ClubConnectService::purchaseMobileAirtime($inputs);
        if (! $request['error']) {
            return response()->json($response['message'], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $user->creditDeduct($amount, 'Purchase Airtime');
        $orderStatus = $response['status'] ?? '';

        if (in_array($orderStatus, ['ORDER_RECEIVED', 'ORDER_PROCESSED', 'ORDER_COMPLETED'])) {
            $airtimeTxn = AirtimeTopup::create([
                'user_id' => $user->id,
                'references' => $reference,
                'provider_id' => $productId,
                'amount' => $amount,
                'phone_no' => $phone,
            ]);

            Transaction::create([
                'user_id' => $user->id,
                'type' => 'debit',
                'references' => $reference,
                'amount' => $amount,
                'status' => 'success',
                'note' => $orderStatus,
            ]);

            return response()->json($airtimeTxn, ResponseAlias::HTTP_OK);
        }

        return response()->json(['message' => 'We encountered some problems please try again'], ResponseAlias::HTTP_BAD_REQUEST);
    }
}
