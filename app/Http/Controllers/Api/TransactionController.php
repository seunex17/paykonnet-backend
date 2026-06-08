<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DebitCard;
use App\Models\Transaction;
use App\Services\PaystackService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class TransactionController extends Controller
{
    public function listTransactions(Request $request)
    {
        $user = $request->user();

        $transactions = Transaction::where('user_id', $user->id)
            ->latest()
            ->take(50)
            ->get();

        return response()->json($transactions, ResponseAlias::HTTP_OK);
    }

    public function verifyAddedCard(Request $request)
    {
        $txnId = $request->query('id');

        if (! $txnId) {
            return response()->json(['message' => 'Transaction reference id is required'], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $user = $request->user();

        try {
            $response = PaystackService::verifyTransaction($txnId);

            if (isset($response['status'])) {
                $data = $response['data'];
                $cardData = $data['authorization'];
                $amount = $data['amount'];

                $addedCardData = DebitCard::create([
                    'user_id' => $user->id,
                    'uuid' => Str::uuid()->toString(),
                    'card_number' => $cardData['bin'].'****'.$cardData['last4'],
                    'issuer' => $cardData['bank'],
                    'country' => $cardData['country_code'],
                    'type' => $cardData['card_type'],
                    'expire' => $cardData['exp_year'],
                    'token' => $cardData['authorization_code'],
                ]);

                $user->creditAdd($amount / 100, "Wallet funding from card {$addedCardData['card_number']}");

                Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'credit',
                    'references' => 'Wallet-'.now()->timestamp,
                    'amount' => $amount / 100,
                    'status' => 'success',
                    'note' => "Wallet funding from card {$addedCardData['card_number']}",
                ]);

                return response()->json($addedCardData, ResponseAlias::HTTP_OK);
            }

            return response()->json([
                'message' => $response['message'] ?? 'Card verification failed',
            ], ResponseAlias::HTTP_BAD_REQUEST);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Can not verify transaction',
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function listDebitCards(Request $request)
    {
        $cards = DebitCard::where('user_id', $request->user()->id)->get();

        return response()->json([
            'cards' => $cards,
        ], ResponseAlias::HTTP_OK);
    }

    public function recentActivity(Request $request)
    {
        $activity = Transaction::where('user_id', $request->user()->id)
            ->latest()
            ->first();

        return response()->json($activity ?? [], ResponseAlias::HTTP_OK);
    }
}
