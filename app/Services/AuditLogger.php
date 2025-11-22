<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    public static function log(string $action, ?string $model = null, ?int $modelId = null, ?array $oldValues = null, ?array $newValues = null): void
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            return;
        }

        AuditLog::create([
            'user_id' => $user->id,
            'action' => $action,
            'model' => $model,
            'model_id' => $modelId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public static function logModelEvent(string $event, Model $model, ?array $oldValues = null, ?array $newValues = null): void
    {
        $action = match($event) {
            'created' => 'create',
            'updated' => 'update',
            'deleted' => 'delete',
            default => $event
        };

        self::log($action, get_class($model), $model->getKey(), $oldValues, $newValues);
    }
}