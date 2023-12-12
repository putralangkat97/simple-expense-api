<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\AccountRequest;
use App\Http\Resources\AccountResource;
use App\Http\Resources\AccountTransactionResource;
use App\Models\Account;
use App\Traits\APIResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class AccountController extends Controller
{
    use APIResponse;

    public function index(string|int $id = null)
    {
        $current_user = Auth::user();
        $account = $id ? new AccountResource(
            Account::accountUser($current_user->id)->where('id', $id)->first()
        ) : AccountResource::collection(
            Account::accountUser($current_user->id)->get()
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

    public function transactionByAccount(string|int $id)
    {
        $current_user = Auth::user();
        return new AccountTransactionResource(
            Account::accountUser($current_user->id)->where('id', $id)->first()
        );
    }

    public function submit(AccountRequest $request, string|int $id = null)
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

    public function delete(string|int $id)
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
