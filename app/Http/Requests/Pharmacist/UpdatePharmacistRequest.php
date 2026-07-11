<?php

declare(strict_types=1);

namespace App\Http\Requests\Pharmacist;

use App\Models\Pharmacist;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdatePharmacistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['Super Admin', 'Administrateur']) === true;
    }

    public function rules(): array
    {
        /** @var Pharmacist|null $pharmacist */
        $pharmacist = $this->route('pharmacist');

        return [
            'photo' => ['sometimes', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'first_name' => ['sometimes', 'string', 'regex:/^[\pL\s\'-]+$/u', 'max:120'],
            'middle_name' => ['nullable', 'string', 'regex:/^[\pL\s\'-]+$/u', 'max:120'],
            'last_name' => ['sometimes', 'string', 'regex:/^[\pL\s\'-]+$/u', 'max:120'],
            'ordinal_number' => ['sometimes', 'string', 'regex:/^[A-Za-z0-9\/._-]+$/', 'max:80', Rule::unique('pharmacists', 'ordinal_number')->ignore($pharmacist?->id)],
            'sex' => ['sometimes', Rule::in(['female', 'male', 'other'])],
            'province_id' => ['sometimes', 'uuid', 'exists:provinces,id'],
            'city_id' => ['sometimes', 'uuid', 'exists:cities,id'],
            'commune_id' => ['sometimes', 'uuid', 'exists:communes,id'],
            'professional_address' => ['sometimes', 'string', 'max:255'],
            'professional_phone' => ['sometimes', 'string', 'regex:/^\+?[0-9\s().-]{7,20}$/', 'max:40'],
            'professional_email' => ['sometimes', 'email:rfc', 'max:255'],
            'professional_status' => ['sometimes', Rule::in(['active', 'inactive'])],
            'registered_at' => ['sometimes', 'date'],
            'practice_started_at' => ['sometimes', 'date'],
            'license_number' => ['sometimes', 'string', 'regex:/^[A-Za-z0-9\/._-]+$/', 'max:100', Rule::unique('pharmacists', 'license_number')->ignore($pharmacist?->id)],
            'license_status' => ['sometimes', Rule::in(['active', 'expired', 'suspended', 'revoked'])],
            'license_expires_at' => ['nullable', 'date'],
            'pharmacy_establishment' => ['sometimes', 'string', 'max:180'],
            'specialization' => ['nullable', 'string', 'max:180'],
        ];
    }
}
