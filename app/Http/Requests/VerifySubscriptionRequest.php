<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifySubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reference' => ['required', 'string'],
            'plan' => ['required', 'string', 'in:monthly,term,annual'],
        ];
    }

    public function messages(): array
    {
        return [
            'reference.required' => 'A Paystack payment reference is required.',
            'plan.in' => 'Plan must be monthly, term, or annual.',
        ];
    }
}
