<?php

namespace App\Http\Requests\Party;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePartyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['sometimes', 'in:customer,supplier,bank'],
            'name' => ['sometimes', 'string', 'max:255'],
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
