<?php

// BIL-02: token deduction | BIL-04: low-balance warning | BIL-05: block when zero

namespace App\Services;

use App\Models\TokenTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TokenService
{
    private const LOW_BALANCE_THRESHOLD = 100;

    /**
     * BIL-02 / BIL-05: deduct tokens for an action.
     * Returns false immediately if the user lacks sufficient balance (BIL-05).
     */
    public function deduct(
        User $user,
        int $amount,
        string $description,
        ?string $refType = null,
        ?int $refId = null,
    ): bool {
        if (! $this->hasBalance($user, $amount)) {
            return false;
        }

        DB::transaction(function () use ($user, $amount, $description, $refType, $refId): void {
            // Lock the row to prevent race conditions
            $user = User::lockForUpdate()->findOrFail($user->id);

            // Re-check inside the transaction after acquiring the lock
            if ($user->token_balance < $amount) {
                throw new \RuntimeException('رصيد التوكنز غير كافٍ.');
            }

            $user->decrement('token_balance', $amount);
            $user->refresh();

            TokenTransaction::create([
                'user_id'        => $user->id,
                'type'           => 'deduction',
                'amount'         => -$amount,
                'balance_after'  => $user->token_balance,
                'description'    => $description,
                'reference_type' => $refType,
                'reference_id'   => $refId,
            ]);
        });

        return true;
    }

    /**
     * BIL-01: credit tokens to a user's balance (purchase, refund, bonus).
     */
    public function credit(
        User $user,
        int $amount,
        string $description,
        ?string $refType = null,
        ?int $refId = null,
        string $type = 'purchase',
    ): void {
        DB::transaction(function () use ($user, $amount, $description, $refType, $refId, $type): void {
            $user->increment('token_balance', $amount);
            $user->refresh();

            TokenTransaction::create([
                'user_id'        => $user->id,
                'type'           => $type,
                'amount'         => $amount,
                'balance_after'  => $user->token_balance,
                'description'    => $description,
                'reference_type' => $refType,
                'reference_id'   => $refId,
            ]);
        });
    }

    /**
     * BIL-05: check if the user has enough tokens for an action.
     */
    public function hasBalance(User $user, int $required): bool
    {
        return $user->token_balance >= $required;
    }

    /**
     * BIL-04: true when balance is below the warning threshold (< 100).
     */
    public function isLow(User $user): bool
    {
        return $user->token_balance < self::LOW_BALANCE_THRESHOLD;
    }
}
