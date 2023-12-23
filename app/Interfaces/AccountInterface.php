<?php

namespace App\Interfaces;

use App\Http\Requests\AccountRequest;

interface AccountInterface
{
    public function getAccounts(string|int $id = null);
    public function getTransactionByAccount(string|int $id);
    public function createOrUpdateAccount(AccountRequest $request, string|int $id = null);
    public function deleteAccount(string|int $id);
}
