<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\TransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Account;
use App\Models\Transaction;
use App\Traits\AccountTrait;
use App\Traits\APIResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class TransactionController extends Controller
{
    use APIResponse, AccountTrait;

    public function index(string|int $id = null)
    {
        $current_user = Auth::user();
        $transaction = $id ? new TransactionResource(
            Transaction::transactionUser($current_user->id)->where('id', $id)->first()
        ) : TransactionResource::collection(
            Transaction::transactionUser($current_user->id)->get()
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

    public function submit(TransactionRequest $request, string|int $id = null)
    {
        $validated = $request->validated();

        if ($validated) {
            DB::beginTransaction();
            try {
                $amount = $validated['amount'];
                $transaction = $id ? Transaction::findOrFail($id) : new Transaction();
                $transaction->transaction_name = $validated['transaction_name'];
                $transaction->amount = $amount;
                $transaction->date = $validated['date'];
                $transaction->type = $validated['type'];
                $transaction->remarks = $validated['remarks'] ?? null;
                $transaction->account_id = $validated['account_id'];
                $transaction->user_id = Auth::user()->id;
                $transaction->save();

                $account = $this->createTransaction(
                    $validated['account_id'],
                    $amount,
                    $validated['type']
                );
                if (!$account) {
                    return $this->failedResponse(
                        message: "account not found.",
                        status_code: 400,
                    );
                }
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

    public function delete(string|int $id)
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
