<x-layouts.shop :title="'Kết quả thanh toán'">
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-950 dark:to-gray-900 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl mx-auto">
            <!-- Status Card -->
            <div class="bg-white dark:bg-gray-900 rounded-lg shadow-lg overflow-hidden mb-8">
                <div class="border-l-4 @if($success) border-green-500 @else border-red-500 @endif px-6 py-8">
                    <div class="flex items-center justify-between mb-2">
                        <h1 class="text-3xl font-bold @if($success) text-green-600 @else text-red-600 @endif">
                            @if($success)
                                ✓ Thanh toán thành công
                            @else
                                ✗ Thanh toán thất bại
                            @endif
                        </h1>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400">
                        @if($success)
                            Thanh toán của bạn đã được xử lý thành công. Chi tiết giao dịch hiển thị bên dưới.
                        @else
                            Không thể xử lý thanh toán. Vui lòng thử lại hoặc liên hệ hỗ trợ.
                        @endif
                    </p>
                </div>
            </div>

            <!-- Details Card -->
            <div class="bg-white dark:bg-gray-900 rounded-lg shadow-lg overflow-hidden">
                <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Chi tiết giao dịch</h2>
                </div>

                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    <!-- Order Code -->
                    <div class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        <div class="flex justify-between items-start">
                            <span class="text-gray-600 dark:text-gray-400 font-medium">Mã đơn hàng</span>
                            <span class="text-gray-900 dark:text-white font-semibold text-right">{{ $txnRef ?? 'N/A' }}</span>
                        </div>
                    </div>

                    <!-- Amount -->
                    <div class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        <div class="flex justify-between items-start">
                            <span class="text-gray-600 dark:text-gray-400 font-medium">Số tiền</span>
                            <span class="text-gray-900 dark:text-white font-semibold text-right">
                                @if($amount)
                                    {{ number_format($amount, 0, '.', ',') }} VNĐ
                                @else
                                    N/A
                                @endif
                            </span>
                        </div>
                    </div>

                    <!-- Payment Content -->
                    <div class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        <div class="flex justify-between items-start">
                            <span class="text-gray-600 dark:text-gray-400 font-medium">Nội dung thanh toán</span>
                            <span class="text-gray-900 dark:text-white font-semibold text-right max-w-md">{{ $orderInfo ?? 'N/A' }}</span>
                        </div>
                    </div>

                    <!-- Response Code -->
                    <div class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        <div class="flex justify-between items-start">
                            <span class="text-gray-600 dark:text-gray-400 font-medium">Mã phản hồi</span>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold @if($responseCode === '00') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 @endif">
                                {{ $responseCode ?? 'N/A' }}
                            </span>
                        </div>
                    </div>

                    <!-- Transaction Code at VNPAY -->
                    <div class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        <div class="flex justify-between items-start">
                            <span class="text-gray-600 dark:text-gray-400 font-medium">Mã giao dịch</span>
                            <span class="text-gray-900 dark:text-white font-semibold text-right">{{ $transactionNo ?? 'N/A' }}</span>
                        </div>
                    </div>

                    <!-- Bank Code -->
                    <div class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        <div class="flex justify-between items-start">
                            <span class="text-gray-600 dark:text-gray-400 font-medium">Mã ngân hàng</span>
                            <span class="text-gray-900 dark:text-white font-semibold text-right">{{ $bankCode ?? 'N/A' }}</span>
                        </div>
                    </div>

                    <!-- Payment Time -->
                    <div class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        <div class="flex justify-between items-start">
                            <span class="text-gray-600 dark:text-gray-400 font-medium">Thời gian thanh toán</span>
                            <span class="text-gray-900 dark:text-white font-semibold text-right">
                                @if($payDate)
                                    {{ \Carbon\Carbon::createFromFormat('YmdHis', $payDate)->format('d/m/Y H:i:s') }}
                                @else
                                    N/A
                                @endif
                            </span>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <div class="flex justify-between items-start">
                            <span class="text-gray-600 dark:text-gray-400 font-medium">Trạng thái</span>
                            <span class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-bold @if($success) bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 @endif">
                                @if($success)
                                    ✓ Thanh toán thành công
                                @else
                                    ✗ Thanh toán thất bại
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
                <a href="/" class="inline-flex items-center justify-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors">
                    ← Về trang chủ
                </a>
                @if($success)
                    <a href="{{ route('app.library.show') }}" class="inline-flex items-center justify-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-colors">
                        Mở thư viện của tôi →
                    </a>
                @else
                    <a href="{{ route('app.shop.index') }}" class="inline-flex items-center justify-center px-6 py-3 bg-orange-600 hover:bg-orange-700 text-white font-semibold rounded-lg transition-colors">
                        Tiếp tục mua sắm →
                    </a>
                @endif
            </div>
        </div>
    </div>
</x-layouts.shop>
