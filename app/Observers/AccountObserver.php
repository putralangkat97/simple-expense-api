<?php

namespace App\Observers;

use App\Models\Account;
use Illuminate\Support\Facades\Cache;

class AccountObserver
{
    /**
     * Handle the Account "created" event.
     */
    public function created(Account $account): void
    {
        Cache::forget('accounts');
    }

    /**
     * Handle the Account "updated" event.
     */
    public function updated(Account $account): void
    {
        Cache::forget('account-' . $account);
    }

    /**
     * Handle the Account "deleted" event.
     */
    public function deleted(Account $account): void
    {
        Cache::forget('account-' . $account);
    }
}
