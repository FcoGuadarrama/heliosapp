<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreIntakeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('receipts.import') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return self::intakeRules(true);
    }

    /** @return array<string, mixed> */
    public static function intakeRules(bool $authenticated): array
    {
        return [
            'client_id' => $authenticated
                ? ['nullable', 'integer', Rule::exists('clients', 'id')->whereNull('deleted_at')]
                : ['prohibited'],
            'name' => [$authenticated ? 'required_without:client_id' : 'required', 'nullable', 'string', 'max:255'],
            'email' => [$authenticated ? 'required_without_all:client_id,phone' : 'required_without:phone', 'nullable', 'email', 'max:255'],
            'phone' => [$authenticated ? 'required_without_all:client_id,email' : 'required_without:email', 'nullable', 'string', 'max:20', 'regex:/^[+0-9() .-]{7,20}$/'],
            'receipt' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'extensions:pdf,jpg,jpeg,png,webp', 'mimetypes:application/pdf,image/jpeg,image/png,image/webp', 'max:'.config('intake.max_file_kb')],
            'privacy_consent' => ['required', 'accepted'],
            'privacy_version' => ['required', 'string', Rule::in([(string) config('intake.privacy_version')])],
        ];
    }
}
