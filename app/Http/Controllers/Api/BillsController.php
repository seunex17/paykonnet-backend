<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AirtimeTopup;
use App\Models\CashLoan;
use App\Models\DataLoan;
use App\Models\DataLoanList;
use App\Models\ElectricBillService;
use App\Services\ClubConnectService;
use App\Services\VTPassService;
use Illuminate\Http\Request;
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
}
