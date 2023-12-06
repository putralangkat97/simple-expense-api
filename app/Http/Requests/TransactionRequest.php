<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'transaction_name' => 'required|min:2|max:100',
            'date' => 'required',
            'amount' => 'required',
            'remarks' => 'nullable|min:2|max:100',
            'type' => 'required|in:in,out',
            'account_id' => 'required',
        ];
    }
}
