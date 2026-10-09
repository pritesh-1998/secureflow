<?php

namespace App\Services;

use App\Models\AdminAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuditLogger
{
    /**
     * Log an admin action to both database and the dedicated security log.
     */
    public static function log(
        string $action,
        string $targetType,
        ?string $targetId = null,
        ?string $targetName = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?Request $request = null
    ): AdminAuditLog {
        $req = $request ?? request();
        $user = Auth::user();
        $ip = $req?->ip();
        $timestamp = now();

        $auditLog = AdminAuditLog::create([
            'user_id' => $user?->id,
            'user_email' => $user?->email,
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId !== null ? (string) $targetId : null,
            'target_name' => $targetName,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $ip,
        ]);

        Log::channel('security')->info("Admin action: {$action}", [
            'user_id' => $user?->id,
            'user_email' => $user?->email,
            'action' => $action,
            'target' => "{$targetType}".($targetName ? ": {$targetName}" : ''),
            'old_value' => $oldValues,
            'new_value' => $newValues,
            'ip_address' => $ip,
            'timestamp' => $timestamp->toIso8601String(),
        ]);

        return $auditLog;
    }

    /**
     * Log a 403 forbidden access attempt on an admin route.
     */
    public static function logForbidden(Request $request): void
    {
        $user = $request->user();

        Log::channel('security')->warning('Forbidden access attempt on admin route', [
            'user_id' => $user?->id,
            'user_email' => $user?->email,
            'route' => $request->path(),
            'method' => $request->method(),
            'ip_address' => $request->ip(),
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
