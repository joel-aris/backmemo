<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Pharmacist;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class PharmacistRepository
{
    public function search(array $filters): LengthAwarePaginator
    {
        $likeOperator = DB::connection()->getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';

        return Pharmacist::query()
            ->when($filters['q'] ?? null, function ($query, string $term) use ($likeOperator): void {
                $query->where(function ($nested) use ($term, $likeOperator): void {
                    foreach (['first_name', 'middle_name', 'last_name', 'ordinal_number', 'license_number', 'qr_code_token', 'public_id', 'pharmacy_establishment'] as $column) {
                        $nested->orWhere($column, $likeOperator, '%' . $term . '%');
                    }
                    $nested
                        ->orWhereHas('province', fn ($relation) => $relation->where('name', $likeOperator, '%' . $term . '%'))
                        ->orWhereHas('city', fn ($relation) => $relation->where('name', $likeOperator, '%' . $term . '%'))
                        ->orWhereHas('commune', fn ($relation) => $relation->where('name', $likeOperator, '%' . $term . '%'));
                });
            })
            ->when($filters['province'] ?? null, fn ($query, string $province) => $query->whereHas('province', fn ($relation) => $relation
                ->where('id', $province)
                ->orWhere('name', $likeOperator, '%' . $province . '%')
                ->orWhere('code', $likeOperator, $province)))
            ->when($filters['commune'] ?? null, fn ($query, string $commune) => $query->whereHas('commune', fn ($relation) => $relation
                ->where('id', $commune)
                ->orWhere('name', $likeOperator, '%' . $commune . '%')))
            ->when($filters['sort_by'] ?? null, function ($query, string $sortBy) use ($filters) {
                $direction = strtoupper($filters['sort_dir'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';

                if ($sortBy === 'province') {
                    $query->leftJoin('provinces', 'pharmacists.province_id', '=', 'provinces.id')
                        ->select('pharmacists.*')
                        ->orderBy('provinces.name', $direction);
                } elseif ($sortBy === 'last_name') {
                    $query->orderBy('last_name', $direction);
                } else {
                    $query->orderBy('registered_at', $direction);
                }
            }, fn ($query) => $query->latest('registered_at'))
            ->paginate((int) ($filters['per_page'] ?? 20));
    }

    public function findPublic(string $qrCode): ?Pharmacist
    {
        return Pharmacist::query()
            ->where('qr_code_token', $qrCode)
            ->orWhere('public_id', $qrCode)
            ->orWhere('ordinal_number', $qrCode)
            ->orWhere('license_number', $qrCode)
            ->first();
    }
}
