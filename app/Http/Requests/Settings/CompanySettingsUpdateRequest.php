<?php

namespace App\Http\Requests\Settings;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompanySettingsUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'company_name' => ['nullable', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'rfc' => ['nullable', 'string', 'max:13', 'regex:/^[A-Za-z0-9]+$/'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,svg,webp', 'max:2048'],
            'address' => ['nullable', 'array'],
            'address.street' => ['nullable', 'string', 'max:255'],
            'address.number' => ['nullable', 'string', 'max:50'],
            'address.interior_number' => ['nullable', 'string', 'max:50'],
            'address.neighborhood' => ['nullable', 'string', 'max:255'],
            'address.municipality' => ['nullable', 'string', 'max:255'],
            'address.state' => ['nullable', 'string', 'max:255'],
            'address.postal_code' => ['nullable', 'string', 'max:10'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'primary_color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondary_color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'timezone' => ['required', 'string', Rule::in(\DateTimeZone::listIdentifiers())],
            'default_currency' => ['required', 'string', 'size:3', 'regex:/^[A-Z]{3}$/'],
            'proposal_validity_days' => ['required', 'integer', 'min:1', 'max:365'],
            'responsible_name' => ['nullable', 'string', 'max:255'],
            'responsible_title' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'company_name' => 'Nombre de la empresa',
            'legal_name' => 'Razón social',
            'rfc' => 'RFC',
            'logo' => 'Logo',
            'address.street' => 'Calle',
            'address.number' => 'Número exterior',
            'address.interior_number' => 'Número interior',
            'address.neighborhood' => 'Colonia',
            'address.municipality' => 'Municipio',
            'address.state' => 'Estado',
            'address.postal_code' => 'Código postal',
            'phone' => 'Teléfono',
            'email' => 'Correo electrónico',
            'website' => 'Sitio web',
            'primary_color' => 'Color primario',
            'secondary_color' => 'Color secundario',
            'timezone' => 'Zona horaria',
            'default_currency' => 'Moneda predeterminada',
            'proposal_validity_days' => 'Vigencia de propuestas (días)',
            'responsible_name' => 'Nombre del responsable',
            'responsible_title' => 'Cargo del responsable',
        ];
    }
}
