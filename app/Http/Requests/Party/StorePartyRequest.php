<?php

namespace App\Http\Requests\Party;

use Illuminate\Foundation\Http\FormRequest;

class StorePartyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'in:customer,supplier,bank'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'opening_balance' => ['nullable', 'numeric', 'min:0'],
            'balance_type' => ['nullable', 'in:receive,give'],
            'notes' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
        ];
    }
}
