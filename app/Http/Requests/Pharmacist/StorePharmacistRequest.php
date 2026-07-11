<?php

declare(strict_types=1);

namespace App\Http\Requests\Pharmacist;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StorePharmacistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['Super Admin', 'Administrateur']) === true;
    }

    public function rules(): array
    {
        return [
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'first_name' => ['required', 'string', 'regex:/^[\pL\s\'-]+$/u', 'max:120'],
            'middle_name' => ['nullable', 'string', 'regex:/^[\pL\s\'-]+$/u', 'max:120'],
            'last_name' => ['required', 'string', 'regex:/^[\pL\s\'-]+$/u', 'max:120'],
            'ordinal_number' => ['required', 'string', 'regex:/^[A-Za-z0-9\/._-]+$/', 'max:80', 'unique:pharmacists,ordinal_number'],
            'sex' => ['required', Rule::in(['female', 'male', 'other'])],
            'province_id' => ['required', 'uuid', 'exists:provinces,id'],
            'city_id' => ['required', 'uuid', 'exists:cities,id'],
            'commune_id' => ['required', 'uuid', 'exists:communes,id'],
            'professional_address' => ['required', 'string', 'max:255'],
            'professional_phone' => ['required', 'string', 'regex:/^\+?[0-9\s().-]{7,20}$/', 'max:40'],
            'professional_email' => ['required', 'email:rfc', 'max:255'],
            'professional_status' => ['required', Rule::in(['active', 'inactive'])],
            'registered_at' => ['required', 'date'],
            'practice_started_at' => ['required', 'date'],
            'license_number' => ['required', 'string', 'regex:/^[A-Za-z0-9\/._-]+$/', 'max:100', 'unique:pharmacists,license_number'],
            'license_status' => ['required', Rule::in(['active', 'expired', 'suspended', 'revoked'])],
            'license_expires_at' => ['nullable', 'date'],
            'pharmacy_establishment' => ['required', 'string', 'max:180'],
            'specialization' => ['nullable', 'string', 'max:180'],
        ];
    }
}
