<?php

namespace App\Repositories;

use App\Http\Requests\TransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Interfaces\TransactionInterface;
use App\Models\Transaction;
use App\Traits\AccountTrait;
use App\Traits\APIResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class TransactionRepository implements TransactionInterface
{
    use APIResponse, AccountTrait;

    public function getTransactions(null|string|int $id = null)
    {
        $current_user = Auth::user();
        $transaction = $id ? new TransactionResource(
            Cache::remember('transaction-' . $id, 60 * 15, function () use ($current_user, $id) {
                return Transaction::with('account')->transactionUser($current_user->id)
                    ->where('id', $id)
                    ->first();
            })
        ) : TransactionResource::collection(
            Cache::remember('transactions', 60 * 15, function () use ($current_user) {
                return Transaction::with('account')->transactionUser($current_user->id)
                    ->orderBy('id', 'desc')
                    ->get();
            })
        );

        if (is_null($transaction->resource)) {
            return $this->failedResponse(
                message: "transaction not found.",
                status_code: 404,
            );
        }

        return $this->successResponse(
            message: $id ? "Transaction detail" : "Transaction lists",
            data: $transaction,
        );
    }

    public function createOrUpdateTransaction(TransactionRequest $request, null|string|int $id = null)
    {
        $validated = $request->validated();

        if ($validated) {
            DB::beginTransaction();
            try {
                $transaction = $id ? Transaction::findOrFail($id) : new Transaction();
                $transaction->transaction_name = $validated['transaction_name'];
                $transaction->date = $validated['date'];
                $transaction->type = $validated['type'];
                $transaction->remarks = $validated['remarks'] ?? null;
                $transaction->account_id = $validated['account_id'];
                $transaction->user_id = Auth::user()->id;

                $account = $this->createTransaction(
                    $validated['account_id'],
                    $validated['amount'],
                    $validated['type'],
                    $transaction
                );
                if (!$account) {
                    return $this->failedResponse(
                        message: "account not found.",
                        status_code: 400,
                    );
                }
                $transaction->amount = $validated['amount'];
                $transaction->save();
                DB::commit();

                $message = $id ? "updated" : "created";
                return $this->successResponse(
                    message: "transaction {$message} successfully",
                    data: $transaction,
                    status_code: 201,
                );
            } catch (Throwable $th) {
                DB::rollBack();

                return $this->failedResponse(
                    message: $th->getMessage(),
                );
            }
        }
    }

    public function deleteTransaction(string|int $id)
    {
        DB::beginTransaction();
        try {
            $transaction = Transaction::findOrFail($id);
            if (!$transaction) {
                return $this->failedResponse(
                    message: "transaction not found.",
                    status_code: 404,
                );
            }

            $account = $this->deleteTransaction(
                $transaction->account_id,
                $transaction->amount,
                $transaction->type
            );
            if (!$account) {
                return $this->failedResponse(
                    message: "account not found.",
                    status_code: 400,
                );
            }

            $transaction->delete();
            DB::commit();

            return $this->successResponse(
                message: "Transaction deleted",
                data: $transaction,
            );
        } catch (Throwable $th) {
            DB::rollBack();
            return $this->failedResponse(
                message: $th->getMessage(),
            );
        }
    }
}
