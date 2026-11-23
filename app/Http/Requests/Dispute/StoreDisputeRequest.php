<?php

namespace App\Http\Requests\Dispute;

use Illuminate\Foundation\Http\FormRequest;

class StoreDisputeRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Only clients and freelancers can file disputes
        return auth()->user()->isClient() || auth()->user()->isFreelancer();
    }

    public function rules(): array
    {
        return [
            'contract_id'  => ['required', 'exists:contracts,id'],
            'milestone_id' => ['nullable', 'exists:milestones,id'],
            'reason'       => ['required', 'string', 'min:50', 'max:5000'],
        ];
    }
}
