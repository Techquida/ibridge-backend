<?php

namespace App\Http\Requests;

use App\Models\Partner;
use App\Models\School;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            // 'exam_board' => ['required', 'string', 'in:WAEC,JAMB'],
            'school_code' => ['nullable', 'string'],
            'referral_code' => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validate school code — must exist, not suspended, not expired
            if ($this->filled('school_code')) {
                $school = School::where('unique_code', $this->school_code)->first();

                if (! $school) {
                    $validator->errors()->add('school_code', 'The provided school code is invalid.');

                    return;
                }

                if ($school->is_suspended) {
                    $validator->errors()->add('school_code', 'This school account has been suspended. Contact your school admin.');

                    return;
                }

                if ($school->subscription_expiry && $school->subscription_expiry->isPast()) {
                    $validator->errors()->add('school_code', 'This school\'s subscription has expired. Contact your school admin to renew.');

                    return;
                }
            }

            // Validate referral code — must exist and partner not suspended
            if ($this->filled('referral_code')) {
                $partner = Partner::where('referral_code', $this->referral_code)->first();

                if (! $partner) {
                    $validator->errors()->add('referral_code', 'The provided referral code is invalid.');

                    return;
                }

                if ($partner->is_suspended) {
                    $validator->errors()->add('referral_code', 'This referral code is no longer active.');

                    return;
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'exam_board.required' => 'Please select your exam board (WAEC or JAMB).',
            'exam_board.in' => 'Exam board must be WAEC or JAMB.',
        ];
    }
}
