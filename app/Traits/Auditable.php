<?php

namespace App\Traits;

trait Auditable
{
    // Models may define a `$auditExclude` property to list attributes that
    // should be omitted from audit logs. The trait does not declare the
    // property to avoid conflicts with model definitions.

    /**
     * Return model attributes with excluded keys removed.
     *
     * @return array<string, mixed>
     */
    public function getAuditSafeAttributes(): array
    {
        $attrs = [];

        try {
            $attrs = $this->getAttributes();
        } catch (\Throwable $e) {
            return [];
        }

        foreach ($this->auditExclude ?? [] as $key) {
            if (array_key_exists($key, $attrs)) {
                unset($attrs[$key]);
            }
        }

        return $attrs;
    }
}
