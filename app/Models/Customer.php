<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use \App\Traits\Auditable;

    protected $fillable = [
        'full_name',
        'phone',
        'whatsapp_number',
        'customer_code',
    ];

    /**
     * Exclude PII from audit logs.
     *
     * @var array<string>
     */
    protected array $auditExclude = [
        'phone',
        'whatsapp_number',
    ];

    /**
     * Create a customer via the service layer.
     *
     * @param  array  $data
     * @return static
     */
    public static function createWithService(array $data): self
    {
        return app(\App\Services\CustomerService::class)->create($data);
    }

    /**
     * Update this customer via the service layer.
     *
     * @param  array  $data
     * @return $this
     */
    public function updateWithService(array $data): self
    {
        return app(\App\Services\CustomerService::class)->update($this, $data);
    }

    /**
     * Find a customer by its customer code using the service.
     *
     * @param  string  $code
     * @return static|null
     */
    public static function findByCustomerCode(string $code): ?self
    {
        return app(\App\Services\CustomerService::class)->findByCode($code);
    }
}
