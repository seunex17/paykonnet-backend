<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AirtimeTopup;
use App\Models\DataLoan;
use App\Models\ElectricBillService;
use Illuminate\Http\JsonResponse;
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

    public function fetchDataLoanList(): JsonResponse
    {
        return $this->notImplemented();
    }

    public function fetchDataLoanLength(): JsonResponse
    {
        return $this->notImplemented();
    }

    public function allDataLoan(): JsonResponse
    {
        return $this->notImplemented();
    }

    public function loadBettingCompanies(): JsonResponse
    {
        return $this->notImplemented();
    }

    public function fetchCashLoanLength(): JsonResponse
    {
        return $this->notImplemented();
    }

    public function fetchUnpaidCashLoan(): JsonResponse
    {
        return $this->notImplemented();
    }

    public function allCashLoan(): JsonResponse
    {
        return $this->notImplemented();
    }

    public function listJambService(): JsonResponse
    {
        return $this->notImplemented();
    }

    private function notImplemented(): JsonResponse
    {
        return response()->json([
            'message' => 'Not implemented',
        ], ResponseAlias::HTTP_NOT_IMPLEMENTED);
    }
}
