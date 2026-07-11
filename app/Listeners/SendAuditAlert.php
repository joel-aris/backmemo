<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\AuditLogCreated;
use Illuminate\Support\Facades\Log;

final class SendAuditAlert
{
    public function handle(AuditLogCreated $event): void
    {
        $log = $event->log;

        if (in_array($log->action, ['auth.login_failed', 'auth.logout_failed', 'pharmacist.delete'])) {
            Log::channel('audit')->alert('Security Alert', [
                'action' => $log->action,
                'ip' => $log->ip_address,
                'user_agent' => $log->user_agent,
                'timestamp' => $log->created_at,
            ]);
        }
    }
}