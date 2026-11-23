<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->isClient();
    }

    public function rules(): array
    {
        return [
            'title'            => ['required', 'string', 'max:255'],
            'description'      => ['required', 'string', 'min:50'],
            'category_id'      => ['nullable', 'exists:categories,id'],
            'budget_min'       => ['nullable', 'numeric', 'min:0'],
            'budget_max'       => ['nullable', 'numeric', 'gte:budget_min'],
            'deadline'         => ['nullable', 'date', 'after:today'],
            'required_skills'  => ['nullable', 'array'],
            'required_skills.*'=> ['string', 'max:50'],
            'status'           => ['sometimes', 'in:draft,open'],
        ];
    }
}
