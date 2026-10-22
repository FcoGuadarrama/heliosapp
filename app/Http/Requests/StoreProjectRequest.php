<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('projects.create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'client_id' => 'required|exists:clients,id',
            'name' => 'required|string|max:255',
            'status' => 'required|in:draft,data_received,analyzing,analyzed,quoted,proposal_sent,won,lost,archived',
            'installation_type' => 'required|in:roof,ground,carport,other',
            'property_type' => 'nullable|string|max:255',
            'street' => 'nullable|string|max:255',
            'exterior_number' => 'nullable|string|max:20',
            'neighborhood' => 'nullable|string|max:255',
            'municipality' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'postal_code' => 'nullable|regex:/^[0-9]{5}$/',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'objectives' => 'nullable|array',
            'property_data' => 'nullable|array',
            'notes' => 'nullable|string',
            'assigned_user_id' => 'nullable|exists:users,id',
        ];
    }
}
