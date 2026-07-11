<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Candidacy extends Model
{
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'cv_path',
        'cv_mime_type',
        'cv_size',
        'motivation_letter_path',
        'motivation_letter_mime_type',
        'motivation_letter_size',
        'notes',
        'status',
        'admin_notes',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'cv_size' => 'integer',
        'motivation_letter_size' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
