<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

final class Pharmacist extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $fillable = [
        'public_id',
        'photo_path',
        'first_name',
        'middle_name',
        'last_name',
        'ordinal_number',
        'sex',
        'province_id',
        'city_id',
        'commune_id',
        'professional_address',
        'professional_phone',
        'professional_email',
        'professional_status',
        'registered_at',
        'practice_started_at',
        'license_number',
        'license_status',
        'license_expires_at',
        'pharmacy_establishment',
        'specialization',
        'verification_hash',
        'qr_code_token',
        'qr_code_signature',
        'public_key',
        'public_key_fingerprint',
    ];

    protected function casts(): array
    {
        return [
            'registered_at' => 'date',
            'practice_started_at' => 'date',
            'license_expires_at' => 'date',
        ];
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function commune(): BelongsTo
    {
        return $this->belongsTo(Commune::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Publicly reachable URL for the pharmacist photo (stored on the "public"
     * disk, required by the web and mobile clients to actually display it —
     * photo_path alone is just an internal storage path).
     */
    protected function photo(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->photo_path ? Storage::disk('public')->url($this->photo_path) : null,
        );
    }
}
