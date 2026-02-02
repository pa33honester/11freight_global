<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    use HasFactory;

    protected $table = 'receipts';

    public $timestamps = false; // immutable record with created_at only

    protected $fillable = [
        'receipt_number',
        'type',
        'linked_id',
        'qr_code',
        'created_at',
    ];

    /**
     * Exclude large or sensitive fields from audit logs.
     *
     * @var array<string>
     */
    protected array $auditExclude = [
        'qr_code',
    ];

    /**
     * Get the receipt image URL if it exists.
     */
    public function getReceiptImageUrlAttribute(): ?string
    {
        $imagePath = "receipts_images/{$this->receipt_number}.png";
        if (file_exists(storage_path("app/public/{$imagePath}"))) {
            return "/storage/{$imagePath}";
        }
        return null;
    }

    public static function types(): array
    {
        return ['PR','WR','SR','AR','DR','SS'];
    }

    /**
     * Generate a receipt code for given type.
     *
     * @param string $type
     * @return string
     */
    public static function generate_code(string $type = 'PR'): string
    {
        $now = now();
        $date = $now->format('Ymd');
        $ms = (int) (microtime(true) * 1000) % 10000;
        $ms = str_pad((string) $ms, 4, '0', STR_PAD_LEFT);

        return sprintf('%s-11F-%s-%s', strtoupper($type), $date, $ms);
    }

    /**
     * Ensure receipt_number is generated when creating if absent.
     */
    protected static function booted()
    {
        static::creating(function (self $model) {
            if (empty($model->receipt_number)) {
                $type = $model->type ?? 'PR';
                $model->receipt_number = self::generate_code($type);
            }
        });
    }
}
