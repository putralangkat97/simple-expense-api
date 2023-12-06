<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'transaction_name' => $this->transaction_name,
            'date' => $this->date,
            'type' => $this->type,
            'remarks' => $this->remarks,
            'amount' => $this->amount,
        ];
    }
}
