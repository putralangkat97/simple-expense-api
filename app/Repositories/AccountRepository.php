<?php

namespace App\Repositories;

use App\Http\Requests\AccountRequest;
use App\Http\Resources\AccountResource;
use App\Http\Resources\AccountTransactionResource;
use App\Interfaces\AccountInterface;
use App\Models\Account;
use App\Traits\APIResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class AccountRepository implements AccountInterface
{
    use APIResponse;

    public function getAccounts(null|string|int $id = null)
    {
        $current_user = Auth::user();
        $account = $id ? new AccountResource(
            Cache::remember('account-' . $id, 60 * 15, function () use ($current_user, $id) {
                return Account::accountUser($current_user->id)->where('id', $id)->first();
            })
        ) : AccountResource::collection(
            Cache::remember('accounts', 60 * 15, function () use ($current_user) {
                return Account::accountUser($current_user->id)->get();
            })
        );

        if (is_null($account->resource)) {
            return $this->failedResponse(
                message: "no account found.",
                status_code: 404,
            );
        }

        return $this->successResponse(
            message: $id ? "Account detail" : "Account lists",
            data: $account,
        );
    }

    public function getTransactionByAccount(string|int $id)
    {
        $current_user = Auth::user();
        $account = new AccountTransactionResource(
            Cache::remember('account-transaction-' . $id, 60 * 15, function () use ($current_user, $id) {
                return Account::with('transactions')->accountUser($current_user->id)
                    ->where('id', $id)
                    ->first();
            })
        );

        return $this->successResponse(
            message: "Account detail",
            data: $account,
        );
    }

    public function createOrUpdateAccount(AccountRequest $request, null|string|int $id = null)
    {
        $validated = $request->validated();

        if ($validated) {
            DB::beginTransaction();
            try {
                $account = $id ? Account::findOrFail($id) : new Account();
                $account->name = $validated['name'];
                $account->description = $validated['description'] ?? null;
                $account->amount = $validated['amount'];
                $account->user_id = Auth::user()->id;
                $account->save();
                DB::commit();

                $message = $id ? "updated" : "created";

                return $this->successResponse(
                    message: "account {$message} successfully",
                    data: $account,
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

    public function deleteAccount(string|int $id)
    {
        DB::beginTransaction();
        try {
            $account = Account::findOrFail($id);
            $account->delete();
            DB::commit();

            return $this->successResponse(
                message: "Account deleted",
                data: $account,
            );
        } catch (Throwable $th) {
            DB::rollBack();
            return $this->failedResponse(
                message: $th->getMessage(),
            );
        }
    }
}
