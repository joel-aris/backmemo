<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Document extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $fillable = [
        'pharmacist_id',
        'owner_id',
        'title',
        'type',
        'path',
        'mime_type',
        'size',
        'sha256_hash',
        'current_sha256_hash',
        'hash_algorithm',
        'issued_at',
        'signature_payload',
        'signature',
        'signature_algorithm',
        'public_key',
        'public_key_fingerprint',
        'trusted_timestamp',
        'integrity_verified_at',
        'integrity_status',
        'proof_metadata',
        'qr_code_token',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'date',
            'trusted_timestamp' => 'datetime',
            'integrity_verified_at' => 'datetime',
            'proof_metadata' => 'array',
            'size' => 'integer',
        ];
    }

    public function pharmacist(): BelongsTo
    {
        return $this->belongsTo(Pharmacist::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
