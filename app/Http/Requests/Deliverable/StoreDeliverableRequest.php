<?php

namespace App\Http\Requests\Deliverable;

use Illuminate\Foundation\Http\FormRequest;

class StoreDeliverableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->isFreelancer();
    }

    public function rules(): array
    {
        return [
            'notes'         => ['nullable', 'string', 'max:5000'],
            'external_link' => ['nullable', 'url', 'max:500'],
            'files'         => ['nullable', 'array', 'max:10'],
            'files.*'       => ['file', 'max:51200'], // Max 50MB per file
        ];
    }
}
