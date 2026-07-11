<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;

Route::get('/{any}', function () {
    $path = public_path('index.html');

    if (File::exists($path)) {
        return response()->file($path);
    }

    return response()->json(['message' => 'Application non trouvee.'], 404);
})->where('any', '.*');
