<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DataBank;
use Illuminate\Http\Request;
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
        // TODO: Implement verifyUserName
        return response()->json(['message' => 'Method not implemented'], ResponseAlias::HTTP_NOT_IMPLEMENTED);
    }

    public function shareData(Request $request)
    {
        // TODO: Implement shareData
        return response()->json(['message' => 'Method not implemented'], ResponseAlias::HTTP_NOT_IMPLEMENTED);
    }

    public function withdrawData(Request $request)
    {
        // TODO: Implement withdrawData
        return response()->json(['message' => 'Method not implemented'], ResponseAlias::HTTP_NOT_IMPLEMENTED);
    }

    public function shareDataAgent(Request $request)
    {
        // TODO: Implement shareDataAgent
        return response()->json(['message' => 'Method not implemented'], ResponseAlias::HTTP_NOT_IMPLEMENTED);
    }
}
