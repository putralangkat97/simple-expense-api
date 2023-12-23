<?php

namespace App\Observers;

use App\Models\Transaction;
use Illuminate\Support\Facades\Cache;

class TransactionObserver
{
    /**
     * Handle the Transaction "created" event.
     */
    public function created(Transaction $transaction): void
    {
        Cache::forget('transactions');
        Cache::forget('account-transaction-' . $transaction->account_id);
    }

    /**
     * Handle the Transaction "updated" event.
     */
    public function updated(Transaction $transaction): void
    {
        Cache::forget('transaction-' . $transaction);
        Cache::forget('account-transaction-' . $transaction->account_id);
    }

    /**
     * Handle the Transaction "deleted" event.
     */
    public function deleted(Transaction $transaction): void
    {
        Cache::forget('transaction-' . $transaction);
        Cache::forget('account-transaction-' . $transaction->account_id);
    }
}
