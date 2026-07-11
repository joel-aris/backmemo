<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\AuditLog;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class AuditLogCreated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly AuditLog $log)
    {
    }
}