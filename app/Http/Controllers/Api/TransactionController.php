<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\GenerateUssdCardMail;
use App\Models\DataLoan;
use App\Models\DataLoanRepayment;
use App\Models\DebitCard;
use App\Models\Transaction;
use App\Models\UssdCard;
use App\Services\PaystackService;
use App\Services\UtilityService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
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

    public function generateUssdCards(Request $request)
    {
        $user = $request->user();
        $inputs = $request->all();
        $qty = (int) ($inputs['quantity'] ?? 1);
        $service = $inputs['product'] ?? null;
        $identifier = now()->timestamp.rand(1000, 9999);
        $amount = (float) ($inputs['amount'] ?? 0);
        $name = $inputs['service'] ?? null;
        $code = $inputs['code'] ?? null;

        $toCharge = $amount * $qty;

        if (! Hash::check($inputs['pin'] ?? '', $user->transfer_pin)) {
            return response()->json(['message' => 'Transaction pin is invalid'], ResponseAlias::HTTP_BAD_REQUEST);
        }

        if ($toCharge < 0) {
            return response()->json(['message' => 'Invalid amount entered'], ResponseAlias::HTTP_BAD_REQUEST);
        }

        if (! $user->hasCredits($toCharge)) {
            return response()->json(['message' => 'Insufficient account balance please refill and try again'], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $user->creditDeduct($toCharge);

        for ($i = 0; $i < $qty; $i++) {

            $cardNumber = UtilityService::generateUniqueCardNumber();

            UssdCard::create([
                'name' => $name,
                'number' => $cardNumber,
                'identifier' => $identifier,
                'service' => $service,
                'code' => $code,
                'amount' => $amount,
                'quantity' => $qty,
            ]);
        }

        Transaction::create([
            'user_id' => $user->id,
            'type' => 'debit',
            'references' => $identifier,
            'amount' => $toCharge,
            'status' => 'success',
            'note' => "Generated {$qty} of paykonet card(s)",
            'source_table' => 'walet',
        ]);

        $cards = UssdCard::where('identifier', $identifier)->get();
        $pdf = Pdf::loadView('pdf.ussd', ['cards' => $cards]);
        $output = $pdf->output();

        Mail::to($user->email)->send(new GenerateUssdCardMail($user, $cards, $output));

        return response()->json([
            'message' => 'USSD cards generated and sent to your email successfully',
            'cards' => $cards,
        ], 200);
    }

    public function loanRepayment(Request $request)
    {
        $user = $request->user();
        $inputs = $request->all();
        $rawAmount = $inputs['amount'] ?? null;
        $loanId = $inputs['id'] ?? null;

        if (! is_numeric($rawAmount)) {
            return response()->json(['message' => 'Invalid Amount entered'], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $amountInKobo = (int) ($rawAmount * 100);

        $loan = DataLoan::find($loanId);

        if (! $loan) {
            return response()->json(['message' => 'Something went wrong'], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $debitCard = DebitCard::find($loan->debit_card_id);

        if (! $debitCard || empty($debitCard->token)) {
            return response()->json(['message' => 'No valid repayment card token found'], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $reference = 'Loan_'.time();

        $data = [
            'amount' => $amountInKobo,
            'authorization_code' => $debitCard->token,
            'email' => $user->email,
            'reference' => $reference,
        ];

        try {
            $response = PaystackService::chargeAuthorization($data);

            if ($response->successful()) {
                $paymentResponse = $response->object(); // Returns JSON as a clean PHP Object graph

                if (! empty($paymentResponse['status']) && $paymentResponse->data['status'] === 'success') {
                    $data = $paymentResponse['data'];
                    $cardData = $data['authorization'];

                    $amountPaid = (float) ($data->amount / 100);

                    $amountJustPaid = (float) $loan->amount_paid + $amountPaid;
                    $fullyPaid = $amountJustPaid >= (float) $loan->amount;

                    $loan->update([
                        'repayment_amount' => max(0, (float) $loan->repayment_amount - $amountPaid),
                        'amount_paid' => $amountJustPaid,
                        'fully_paid' => $fullyPaid,
                    ]);

                    DataLoanRepayment::create([
                        'user_id' => $user->id,
                        'data_loan_id' => $loan->id,
                        'amount' => $amountPaid,
                    ]);

                    $debitCard->update([
                        'token' => $cardData->authorization_code,
                    ]);

                    return response()->json([
                        'message' => 'Loan repayment has been deducted from your debit card.',
                    ], ResponseAlias::HTTP_OK);
                }
            }

            return response()->json(['message' => 'Loan repayment failed'], 422);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Loan repayment failed'], 422);
        }
    }

    public function cashLoanRepayment(Request $request)
    {
        // TODO: Implement cashLoanRepayment
        return response()->json(['message' => 'Method not implemented'], ResponseAlias::HTTP_NOT_IMPLEMENTED);
    }
}
