<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Province extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $fillable = ['name', 'code'];

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }

    public function communes(): HasMany
    {
        return $this->hasMany(Commune::class);
    }

    public function pharmacists(): HasMany
    {
        return $this->hasMany(Pharmacist::class);
    }
}