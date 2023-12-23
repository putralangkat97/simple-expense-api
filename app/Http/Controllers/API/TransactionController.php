<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\TransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Repositories\TransactionRepository;
use App\Traits\AccountTrait;
use App\Traits\APIResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class TransactionController extends Controller
{
    public function __construct(protected TransactionRepository $transaction)
    {
    }

    public function index(string|int $id = null)
    {
        return $this->transaction->getTransactions($id);
    }

    public function submit(TransactionRequest $request, string|int $id = null)
    {
        return $this->transaction->createOrUpdateTransaction($request, $id);
    }

    public function delete(string|int $id)
    {
        return $this->transaction->deleteTransaction($id);
    }
}
