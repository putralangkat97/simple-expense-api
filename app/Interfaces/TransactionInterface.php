<?php

namespace App\Interfaces;

use App\Http\Requests\TransactionRequest;

interface TransactionInterface
{
    public function getTransactions(string|int $id = null);
    public function createOrUpdateTransaction(TransactionRequest $request, string|int $id = null);
    public function deleteTransaction(string|int $id);
}
