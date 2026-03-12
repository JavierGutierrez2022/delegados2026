<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;

trait LogsActivity
{
    // Campos que NO se guardan en 'before/after'
    protected array $activityHidden = ['updated_at','created_at','password','remember_token'];

    public static function bootLogsActivity()
    {
        static::created(function($model){
            self::writeActivity($model, 'CREATED', [], $model->getAttributes(), 'Creación');
        });

        static::updated(function($model){
            // Valores antes y después
            $before = Arr::except($model->getOriginal(), $model->activityHidden ?? []);
            $after  = Arr::except($model->getAttributes(), $model->activityHidden ?? []);

            // Si no hubo cambios “reales”, nada
            if (empty(array_diff_assoc($after, $before))) return;

            self::writeActivity($model, 'UPDATED', $before, $after, 'Actualización');
        });

        static::deleted(function($model){
            self::writeActivity($model, 'DELETED', $model->getOriginal(), [], 'Eliminación');
        });
    }

    protected static function writeActivity($model, string $action, array $before, array $after, ?string $desc=null)
    {
        try {
            ActivityLog::create([
                'user_id'    => Auth::id(),
                'action'     => $action,
                'model_type' => get_class($model),
                'model_id'   => $model->getKey(),
                'url'        => request()->fullUrl() ?? null,
                'method'     => request()->method() ?? null,
                'ip'         => request()->ip() ?? null,
                'user_agent' => request()->userAgent() ?? null,
                'description'=> $desc,
                'before'     => $before ? $before : null,
                'after'      => $after  ? $after  : null,
            ]);
        } catch (\Throwable $e) {
            // No romper el flujo si por algún motivo fallara
            report($e);
        }
    }

    /** Para registrar algo ad-hoc */
    public static function logCustom(?int $userId, string $desc, array $context = [])
    {
        ActivityLog::create([
            'user_id' => $userId,
            'action'  => 'CUSTOM',
            'description' => $desc,
            'url' => request()->fullUrl() ?? null,
            'method' => request()->method() ?? null,
            'ip' => request()->ip() ?? null,
            'user_agent' => request()->userAgent() ?? null,
            'before' => $context['before'] ?? null,
            'after'  => $context['after']  ?? null,
            'model_type' => $context['model_type'] ?? null,
            'model_id'   => $context['model_id'] ?? null,
        ]);
    }
}
