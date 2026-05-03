<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;

class AuditLogger
{
    public static function log(?User $user, string $action, mixed $subject = null, array $meta = []): void
    {
        AuditLog::query()->create([
            'user_id' => $user?->id,
            'action' => $action,
            'subject_type' => is_object($subject) ? $subject::class : null,
            'subject_id' => is_object($subject) && isset($subject->id) ? (string) $subject->id : null,
            'meta' => $meta,
        ]);
    }
}
