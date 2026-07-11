<?php

declare(strict_types=1);

namespace App\Http\Requests\Document;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\Document::class) === true;
    }

    public function rules(): array
    {
        return [
            'pharmacist_id' => ['nullable', 'uuid', 'exists:pharmacists,id'],
            'title' => ['required', 'string', 'max:180'],
            'type' => ['required', Rule::in(['license', 'certificate', 'authorization', 'identity', 'inspection_report', 'other'])],
            'issued_at' => ['required', 'date'],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
        ];
    }
}
