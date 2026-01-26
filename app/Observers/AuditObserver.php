<?php

namespace App\Observers;

use App\Services\AuditLogService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class AuditObserver
{
    protected AuditLogService $service;

    public function __construct()
    {
        $this->service = app(AuditLogService::class);
    }

    /**
     * Derive a module name from the model class.
     *
     * @param object $model
     */
    protected function moduleName($model): string
    {
        return Str::snake(class_basename($model));
    }

    /**
     * Return model attributes excluding any audit-excluded keys.
     *
     * @param object $model
     * @return array<string,mixed>
     */
    protected function safeAttributes($model): array
    {
        try {
            if (method_exists($model, 'getAuditSafeAttributes')) {
                return $model->getAuditSafeAttributes();
            }

            $attrs = $model->getAttributes();

            if (property_exists($model, 'auditExclude') && is_array($model->auditExclude)) {
                foreach ($model->auditExclude as $k) {
                    if (array_key_exists($k, $attrs)) {
                        unset($attrs[$k]);
                    }
                }
            }

            return $attrs;
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function created($model)
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable|null $user */
            $user = Auth::user();
        $userId = $user instanceof \Illuminate\Contracts\Auth\Authenticatable
            ? $user->getAuthIdentifier()
            : null;

        $this->service->log(
            $userId,
            'CREATED',
            $this->moduleName($model),
            null,
            $this->safeAttributes($model),
            request()?->ip() ?? null
        );
    }

    public function updated($model)
    {
        $old = method_exists($model, 'getOriginal') ? $model->getOriginal() : null;
        $new = $this->safeAttributes($model);
        /** @var \Illuminate\Contracts\Auth\Authenticatable|null $user */
            $user = Auth::user();
        $userId = $user instanceof \Illuminate\Contracts\Auth\Authenticatable
            ? $user->getAuthIdentifier()
            : null;

        $this->service->log(
            $userId,
            'UPDATED',
            $this->moduleName($model),
            $old,
            $new,
            request()?->ip() ?? null
        );
    }

    public function deleted($model)
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable|null $user */
            $user = Auth::user();
        $userId = $user instanceof \Illuminate\Contracts\Auth\Authenticatable
            ? $user->getAuthIdentifier()
            : null;

        $this->service->log(
            $userId,
            'DELETED',
            $this->moduleName($model),
            $this->safeAttributes($model),
            null,
            request()?->ip() ?? null
        );
    }
}
