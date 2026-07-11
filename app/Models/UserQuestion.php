<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class UserQuestion extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'category',
        'question',
        'answer',
        'is_read',
        'is_answered',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'is_read' => 'boolean',
        'is_answered' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
