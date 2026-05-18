<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemConfig extends Model
{
    use HasFactory;

    public const string KEY_COMMISSION_RATE = 'commission_rate';

    public const string KEY_FEATURED_PRODUCTS_COUNT = 'featured_products_count';

    public const string KEY_MAX_WITHDRAWAL_AMOUNT = 'max_withdrawal_amount';

    public const string TYPE_BOOLEAN = 'boolean';

    public const string TYPE_DECIMAL = 'decimal';

    public const string TYPE_INTEGER = 'integer';

    public const string TYPE_STRING = 'string';

    /**
     * @var array<string, array{value: string, description: string, type: string, min?: int|float, max?: int|float, nullable?: bool, notifies_sellers?: bool}>
     */
    public const array MANAGED_CONFIGS = [
        self::KEY_COMMISSION_RATE => [
            'value'            => '10',
            'description'      => 'Phí hoa hồng cơ bản (%) thu của người bán trên mỗi đơn hàng thành công',
            'type'             => self::TYPE_DECIMAL,
            'min'              => 0,
            'max'              => 100,
            'notifies_sellers' => true,
        ],
        'min_withdrawal_amount' => [
            'value'       => '10000',
            'description' => 'Số tiền rút tối thiểu (VND) khi Seller yêu cầu rút tiền về tài khoản ngân hàng',
            'type'        => self::TYPE_DECIMAL,
            'min'         => 0,
        ],
        self::KEY_MAX_WITHDRAWAL_AMOUNT => [
            'value'       => '',
            'description' => 'Số tiền rút tối đa (VND) cho mỗi yêu cầu. Để trống nếu không giới hạn ngoài số dư ví.',
            'type'        => self::TYPE_DECIMAL,
            'min'         => 0,
            'nullable'    => true,
        ],
        'platform_payout_enabled' => [
            'value'       => 'true',
            'description' => 'Bật/tắt cơ chế payout lợi nhuận định kỳ cho chủ nền tảng',
            'type'        => self::TYPE_BOOLEAN,
        ],
        'platform_payout_auto_process' => [
            'value'       => 'true',
            'description' => 'Tự động xử lý payout lợi nhuận khi batch đủ điều kiện được tạo',
            'type'        => self::TYPE_BOOLEAN,
        ],
        'platform_payout_settlement_days' => [
            'value'       => '7',
            'description' => 'Số ngày chờ sau khi order item hoàn tất trước khi được tính vào payout lợi nhuận',
            'type'        => self::TYPE_INTEGER,
            'min'         => 1,
        ],
        'platform_payout_bank_name' => [
            'value'       => 'Vietcombank',
            'description' => 'Tên ngân hàng nhận payout lợi nhuận nền tảng',
            'type'        => self::TYPE_STRING,
        ],
        'platform_payout_bank_code' => [
            'value'       => 'VCB',
            'description' => 'Mã ngân hàng nhận payout lợi nhuận nền tảng',
            'type'        => self::TYPE_STRING,
        ],
        'platform_payout_bank_account_number' => [
            'value'       => '0123456789',
            'description' => 'Số tài khoản nhận payout lợi nhuận nền tảng',
            'type'        => self::TYPE_STRING,
        ],
        'platform_payout_bank_account_name' => [
            'value'       => 'KEYCOVE OWNER',
            'description' => 'Tên tài khoản nhận payout lợi nhuận nền tảng',
            'type'        => self::TYPE_STRING,
        ],
        'order_auto_complete_days' => [
            'value'       => '7',
            'description' => 'Số ngày sau khi giao key mà không có khiếu nại thì order item tự động hoàn tất',
            'type'        => self::TYPE_INTEGER,
            'min'         => 1,
        ],
        self::KEY_FEATURED_PRODUCTS_COUNT => [
            'value'       => '8',
            'description' => 'Số sản phẩm nổi bật hiển thị trên trang cửa hàng',
            'type'        => self::TYPE_INTEGER,
            'min'         => 1,
            'max'         => 100,
        ],
    ];

    public $timestamps = false;

    protected $fillable = [
        'key',
        'value',
        'description',
    ];

    /**
     * @return array<int, string>
     */
    public static function managedKeys(): array
    {
        return array_keys(self::MANAGED_CONFIGS);
    }

    /**
     * @return array{value: string, description: string, type: string, min?: int|float, max?: int|float, nullable?: bool, notifies_sellers?: bool}|null
     */
    public static function managedDefinition(string $key): ?array
    {
        return self::MANAGED_CONFIGS[$key] ?? null;
    }

    public static function isManagedKey(string $key): bool
    {
        return array_key_exists($key, self::MANAGED_CONFIGS);
    }

    /**
     * @return array<int, string>
     */
    public static function validationRules(string $key, string $value): array
    {
        $definition = self::managedDefinition($key);

        if ($definition === null) {
            return ['prohibited'];
        }

        if (($definition['nullable'] ?? false) && $value === '') {
            return ['nullable', 'string', 'max:255'];
        }

        $rules = match ($definition['type']) {
            self::TYPE_BOOLEAN => ['required', 'string', 'in:true,false,1,0'],
            self::TYPE_DECIMAL => ['required', 'numeric'],
            self::TYPE_INTEGER => ['required', 'integer'],
            default            => ['required', 'string', 'max:255'],
        };

        if (array_key_exists('min', $definition)) {
            $rules[] = 'min:'.$definition['min'];
        }

        if (array_key_exists('max', $definition)) {
            $rules[] = 'max:'.$definition['max'];
        }

        return $rules;
    }

    public static function notifiesSellers(string $key): bool
    {
        return (bool) (self::managedDefinition($key)['notifies_sellers'] ?? false);
    }

    /**
     * @param  Builder<SystemConfig>  $query
     * @return Builder<SystemConfig>
     */
    public function scopeManaged(Builder $query): Builder
    {
        return $query->whereIn('key', self::managedKeys());
    }
}
