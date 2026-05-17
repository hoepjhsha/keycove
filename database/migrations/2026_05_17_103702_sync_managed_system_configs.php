<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $managedConfigs = [
            [
                'key'         => 'commission_rate',
                'value'       => '10',
                'description' => 'Phí hoa hồng cơ bản (%) thu của người bán trên mỗi đơn hàng thành công',
            ],
            [
                'key'         => 'featured_products_count',
                'value'       => '8',
                'description' => 'Số sản phẩm nổi bật hiển thị trên trang cửa hàng',
            ],
            [
                'key'         => 'max_withdrawal_amount',
                'value'       => '',
                'description' => 'Số tiền rút tối đa (VND) cho mỗi yêu cầu. Để trống nếu không giới hạn ngoài số dư ví.',
            ],
            [
                'key'         => 'min_withdrawal_amount',
                'value'       => '10000',
                'description' => 'Số tiền rút tối thiểu (VND) khi Seller yêu cầu rút tiền về tài khoản ngân hàng',
            ],
            [
                'key'         => 'order_auto_complete_days',
                'value'       => '7',
                'description' => 'Số ngày sau khi giao key mà không có khiếu nại thì order item tự động hoàn tất',
            ],
            [
                'key'         => 'platform_payout_auto_process',
                'value'       => 'true',
                'description' => 'Tự động xử lý payout lợi nhuận khi batch đủ điều kiện được tạo',
            ],
            [
                'key'         => 'platform_payout_bank_account_name',
                'value'       => 'KEYCOVE OWNER',
                'description' => 'Tên tài khoản nhận payout lợi nhuận nền tảng',
            ],
            [
                'key'         => 'platform_payout_bank_account_number',
                'value'       => '0123456789',
                'description' => 'Số tài khoản nhận payout lợi nhuận nền tảng',
            ],
            [
                'key'         => 'platform_payout_bank_code',
                'value'       => 'VCB',
                'description' => 'Mã ngân hàng nhận payout lợi nhuận nền tảng',
            ],
            [
                'key'         => 'platform_payout_bank_name',
                'value'       => 'Vietcombank',
                'description' => 'Tên ngân hàng nhận payout lợi nhuận nền tảng',
            ],
            [
                'key'         => 'platform_payout_enabled',
                'value'       => 'true',
                'description' => 'Bật/tắt cơ chế payout lợi nhuận định kỳ cho chủ nền tảng',
            ],
            [
                'key'         => 'platform_payout_settlement_days',
                'value'       => '7',
                'description' => 'Số ngày chờ sau khi order item hoàn tất trước khi được tính vào payout lợi nhuận',
            ],
        ];

        DB::table('system_configs')->insertOrIgnore($managedConfigs);

        DB::table('system_configs')
            ->whereNotIn('key', array_column($managedConfigs, 'key'))
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Deleted ad-hoc settings cannot be restored reliably.
    }
};
