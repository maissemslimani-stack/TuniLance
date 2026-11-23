<?php

namespace App\Http\Requests\Submission;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->isFreelancer();
    }

    public function rules(): array
    {
        return [
            'bid_amount'    => ['required', 'numeric', 'min:1'],
            'cover_letter'  => ['required', 'string', 'min:100', 'max:3000'],
            'delivery_days' => ['required', 'integer', 'min:1', 'max:365'],
        ];
    }
}
