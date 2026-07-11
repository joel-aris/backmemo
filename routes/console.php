<?php

declare(strict_types=1);

use App\Models\Pharmacist;
use App\Services\PharmacistService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('sanctum:prune-expired --hours=24')->daily();

Artisan::command('pharmacists:recalculate-proofs', function (PharmacistService $service): void {
    $count = 0;

    Pharmacist::query()->chunkById(50, function ($pharmacists) use ($service, &$count): void {
        foreach ($pharmacists as $pharmacist) {
            $service->recalculateProof($pharmacist);
            $count++;
        }
    });

    $this->info("Preuves cryptographiques recalculees pour {$count} pharmacien(s).");
})->purpose("Recalcule le hash de verification, la signature et l'appartenance au registre Merkle pour tous les pharmaciens");
