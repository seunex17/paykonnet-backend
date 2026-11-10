<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class PurchaseProviderService
{
    /**
     * @param  callable(): array<string, mixed>  $purchase
     * @return array<string, mixed>|JsonResponse
     */
    public static function call(User $user, float $amount, string $reference, callable $purchase): array|JsonResponse
    {
        try {
            return $purchase();
        } catch (Throwable $exception) {
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'debit',
                'references' => $reference,
                'amount' => $amount,
                'status' => 'pending',
                'note' => 'Provider outcome unknown; verify delivery before refunding or retrying.',
            ]);

            Log::error('Purchase provider outcome unknown', [
                'reference' => $reference,
                'user_id' => $user->id,
                'exception' => $exception::class,
            ]);

            return response()->json([
                'message' => 'Your purchase is pending confirmation. Please do not retry; contact support with this reference.',
                'status' => 'pending',
                'references' => $reference,
            ], 202);
        }
    }
}
