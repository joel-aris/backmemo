<?php

declare(strict_types=1);

namespace App\Http\Requests\Ocr;

use Illuminate\Foundation\Http\FormRequest;

final class ExtractOcrRequest extends FormRequest
{
    public function authorize(): bool
    {
        // /ocr/extract is intentionally public (see routes/api.php): it's
        // used from the unauthenticated public candidacy form.
        return true;
    }

    public function rules(): array
    {
        return [
            'document' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
        ];
    }
}
