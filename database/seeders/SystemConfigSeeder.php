<?php

namespace Database\Seeders;

use App\Models\SystemConfig;
use Illuminate\Database\Seeder;

class SystemConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $configs = [
            // ==========================================
            // 1. TÀI CHÍNH & PHÍ (FINANCE & FEES)
            // ==========================================
            [
                'key'         => 'commission_rate',
                'value'       => '10',
                'description' => 'Phí hoa hồng cơ bản (%) thu của người bán trên mỗi đơn hàng thành công',
            ],
            [
                'key'         => 'fixed_transaction_fee',
                'value'       => '0',
                'description' => 'Phí cố định cộng thêm thu của người bán trên mỗi đơn hàng (VND) - Hiện để 0',
            ],
            [
                'key'         => 'min_withdrawal_amount',
                'value'       => '10000',
                'description' => 'Số tiền rút tối thiểu (VND) khi Seller yêu cầu rút tiền về tài khoản ngân hàng',
            ],
            [
                'key'         => 'withdrawal_fee_percent',
                'value'       => '0',
                'description' => 'Phí xử lý giao dịch rút tiền (%) - Thường miễn phí để thu hút Seller',
            ],
            [
                'key'         => 'platform_payout_enabled',
                'value'       => 'true',
                'description' => 'Bật/tắt cơ chế payout lợi nhuận định kỳ cho chủ nền tảng',
            ],
            [
                'key'         => 'platform_payout_auto_process',
                'value'       => 'true',
                'description' => 'Tự động xử lý payout lợi nhuận khi batch đủ điều kiện được tạo',
            ],
            [
                'key'         => 'platform_payout_settlement_days',
                'value'       => '7',
                'description' => 'Số ngày chờ sau khi order item hoàn tất trước khi được tính vào payout lợi nhuận',
            ],
            [
                'key'         => 'platform_payout_bank_name',
                'value'       => 'Vietcombank',
                'description' => 'Tên ngân hàng nhận payout lợi nhuận nền tảng',
            ],
            [
                'key'         => 'platform_payout_bank_code',
                'value'       => 'VCB',
                'description' => 'Mã ngân hàng nhận payout lợi nhuận nền tảng',
            ],
            [
                'key'         => 'platform_payout_bank_account_number',
                'value'       => '0123456789',
                'description' => 'Số tài khoản nhận payout lợi nhuận nền tảng',
            ],
            [
                'key'         => 'platform_payout_bank_account_name',
                'value'       => 'KEYCOVE OWNER',
                'description' => 'Tên tài khoản nhận payout lợi nhuận nền tảng',
            ],

            // ==========================================
            // 2. VẬN HÀNH ĐƠN HÀNG (ORDERS & ESCROW)
            // ==========================================
            [
                'key'         => 'escrow_duration_hours',
                'value'       => '72',
                'description' => 'Thời gian giữ tiền (Escrow) sau khi giao key trước khi cộng vào ví khả dụng của Seller (Giờ)',
            ],
            [
                'key'         => 'order_auto_complete_days',
                'value'       => '7',
                'description' => 'Số ngày sau khi giao key mà không có khiếu nại thì order item tự động hoàn tất',
            ],
            [
                'key'         => 'order_payment_timeout_minutes',
                'value'       => '15',
                'description' => 'Thời gian tối đa để người mua thanh toán trước khi đơn hàng tự động bị hủy (Phút)',
            ],
            [
                'key'         => 'max_keys_per_listing',
                'value'       => '1000',
                'description' => 'Số lượng key tối đa cho phép thêm vào một sản phẩm/biến thể',
            ],

            // ==========================================
            // 3. RỦI RO & KHIẾU NẠI (RISK & DISPUTES)
            // ==========================================
            [
                'key'         => 'kyc_required_for_sellers',
                'value'       => 'true',
                'description' => 'Bắt buộc xác minh danh tính (KYC CCCD/CMND) mới được phép tạo gian hàng',
            ],
            [
                'key'         => 'auto_approve_products',
                'value'       => 'false',
                'description' => 'Sản phẩm Seller đăng lên có tự động duyệt không? (false = Admin phải duyệt tay)',
            ],
            [
                'key'         => 'dispute_response_deadline_hours',
                'value'       => '48',
                'description' => 'Thời gian tối đa Seller phải phản hồi khiếu nại (Giờ), quá hạn tự động hoàn tiền cho Buyer',
            ],
            [
                'key'         => 'max_dispute_window_days',
                'value'       => '3',
                'description' => 'Số ngày tối đa kể từ lúc mua mà người mua được phép mở khiếu nại',
            ],

            // ==========================================
            // 4. HỆ THỐNG & HIỂN THỊ (SYSTEM & UI)
            // ==========================================
            [
                'key'         => 'site_name',
                'value'       => 'KeyCove',
                'description' => 'Tên hiển thị của website',
            ],
            [
                'key'         => 'site_description',
                'value'       => 'Sàn giao dịch Bản quyền Số, Game Key và Phầm mềm uy tín',
                'description' => 'Mô tả Meta dùng cho SEO',
            ],
            [
                'key'         => 'support_email',
                'value'       => 'support@keycove.vn',
                'description' => 'Email hỗ trợ chăm sóc khách hàng',
            ],
            [
                'key'         => 'hotline',
                'value'       => '1900 9999',
                'description' => 'Số điện thoại đường dây nóng',
            ],
            //            [
            //                'key'         => 'maintenance_mode',
            //                'value'       => 'false',
            //                'description' => 'Bật/tắt chế độ bảo trì toàn hệ thống (true/false)',
            //            ],
        ];

        foreach ($configs as $config) {
            SystemConfig::firstOrCreate(
                ['key' => $config['key']],
                [
                    'value'       => $config['value'],
                    'description' => $config['description'],
                ]
            );
        }
    }
}
