<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\APIResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

class AuthController extends Controller
{
    use APIResponse;

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|min:2|max:255',
            'email' => 'required|email|min:2|max:100|unique:users,email',
            'password' => 'required|min:8|confirmed'
        ]);

        if ($validated) {
            DB::beginTransaction();
            try {
                $user = new User();
                $user->name = $validated['name'];
                $user->email = $validated['email'];
                $user->password = Hash::make($validated['password']);
                $user->save();
                DB::commit();

                return $this->successResponse(
                    message: "User created successfully",
                    data: $user,
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

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|min:2|max:100',
            'password' => 'required|min:8'
        ]);

        if ($validated) {
            $user = User::where('email', $validated['email'])->first();
            if (!$user || !Hash::check($validated['password'], $user->password)) {
                return $this->failedResponse(
                    message: "incorrect email or password.",
                    status_code: 401
                );
            }

            $token = $user->createToken("my_token")->plainTextToken;
            $data = [
                'user' => $user,
                'token_type' => 'Bearer',
                'token' => $token,
            ];

            return $this->successResponse(
                message: "Logged in successfully",
                data: $data,
                status_code: 200,
            );
        }
    }

    public function logout()
    {
        Auth::user()->tokens()->delete();
        return response()->json([
            'message' => 'logout success.'
        ], 200);
    }
}
