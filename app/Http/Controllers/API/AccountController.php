<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\AccountRequest;
use App\Repositories\AccountRepository;

class AccountController extends Controller
{
    public function __construct(protected AccountRepository $account)
    {
    }

    public function index(string|int $id = null)
    {
        return $this->account->getAccounts($id);
    }

    public function transactionByAccount(string|int $id)
    {
        return $this->account->getTransactionByAccount($id);
    }

    public function submit(AccountRequest $request, string|int $id = null)
    {
        return $this->account->createOrUpdateAccount($request, $id);
    }

    public function delete(string|int $id)
    {
        return $this->account->deleteAccount($id);
    }
}
