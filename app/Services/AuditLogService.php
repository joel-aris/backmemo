<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\AuditLogCreated;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

final class AuditLogService
{
    public function record(Request $request, string $action, mixed $resource = null, array $metadata = []): AuditLog
    {
        $previous = AuditLog::query()->latest('created_at')->first();
        $resourceType = is_object($resource) ? $resource::class : null;
        $resourceId = is_object($resource) && isset($resource->id) ? (string) $resource->id : null;
        $createdAt = Carbon::now('UTC');
        $payload = json_encode([
            'user_id' => $request->user()?->id,
            'action' => $action,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => $metadata,
            'previous_hash' => $previous?->entry_hash,
            'created_at' => $createdAt->toIso8601String(),
        ], JSON_THROW_ON_ERROR);

        $log = AuditLog::query()->create([
            'user_id' => $request->user() instanceof User ? $request->user()->id : null,
            'action' => $action,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
            'metadata' => $metadata,
            'previous_hash' => $previous?->entry_hash,
            'entry_hash' => hash('sha256', $payload),
            'created_at' => $createdAt,
        ]);

        AuditLogCreated::dispatch($log);

        return $log;
    }
}
