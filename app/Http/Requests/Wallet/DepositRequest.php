<?php

namespace App\Http\Requests\Wallet;

use Illuminate\Foundation\Http\FormRequest;

class DepositRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'amount'    => ['required', 'numeric', 'min:1', 'max:50000'], // Max single deposit 50,000 TND
            'reference' => ['nullable', 'string', 'max:255'],             // Payment gateway ref
        ];
    }
}
