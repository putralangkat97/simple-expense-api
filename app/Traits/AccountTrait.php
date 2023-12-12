<?php

namespace App\Traits;

use App\Models\Account;

trait AccountTrait
{
    public function createTransaction(
        string|int $account_id,
        int|float $amount,
        string $type,
        array|object $transaction = null
    ): bool {
        if ($transaction->id) {
            $this->deleteTransaction($account_id, $transaction->amount, $type);
        }
        $account = Account::findOrFail($account_id);
        if (!$account) {
            return false;
        }

        if ($type == "in") {
            $account->amount = $account->amount + $amount;
        } else {
            $account->amount = $account->amount - $amount;
        }
        $account->save();
        return true;
    }

    public function deleteTransaction(
        string|int $account_id,
        int|float $amount,
        string $type
    ): bool {
        $account = Account::findOrFail($account_id);
        if (!$account) {
            return false;
        }

        if ($type == "in") {
            $account->amount = $account->amount - $amount;
        } else {
            $account->amount = $account->amount + $amount;
        }
        $account->save();
        return true;
    }
}
