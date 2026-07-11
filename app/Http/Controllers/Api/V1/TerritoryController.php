<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use Illuminate\Routing\Controller;
use App\Models\City;
use App\Models\Commune;
use App\Models\Province;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class TerritoryController extends Controller
{
    public function provinces(Request $request): JsonResponse
    {
        $provinces = Province::query()
            ->select('id', 'name', 'code')
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $provinces]);
    }

    public function cities(Request $request): JsonResponse
    {
        $provinces = $request->query('provinces');

        $query = City::query()->select('id', 'province_id', 'name', 'code');

        if ($provinces) {
            $provinceIds = is_array($provinces) ? $provinces : explode(',', (string) $provinces);
            $query->whereIn('province_id', $provinceIds);
        }

        $cities = $query->orderBy('name')->get();

        return response()->json(['data' => $cities]);
    }

    public function communes(Request $request): JsonResponse
    {
        $cities = $request->query('cities');

        $query = Commune::query()->select('id', 'province_id', 'city_id', 'name');

        if ($cities) {
            $cityIds = is_array($cities) ? $cities : explode(',', (string) $cities);
            $query->whereIn('city_id', $cityIds);
        }

        $communes = $query->orderBy('name')->get();

        return response()->json(['data' => $communes]);
    }
}