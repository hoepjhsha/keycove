<footer class="bg-[#FCF9F4] border-t border-gray-200 pt-16 pb-8 dark:bg-gray-900 dark:border-gray-800 sm:pt-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-10 md:grid-cols-2 lg:grid-cols-4 lg:gap-8">

            <div class="space-y-5">
                <a href="/" class="flex items-center gap-2">
                    <img class="block dark:hidden max-h-10 w-auto object-contain"
                         src="{{ Vite::asset('resources/images/logo-light-horizontal.png') }}"
                         alt="logo light" />
                </a>
                <p class="text-[15px] font-medium text-black dark:text-gray-300 leading-relaxed">
                    Key game và phần mềm bản quyền cho nhu cầu số của bạn, giá rõ ràng và giao key nhanh sau khi thanh toán.
                </p>
                <div class="flex space-x-5 pt-2">
                    <a href="#" class="text-black hover:text-[#D32F2F] dark:text-white transition-colors"><i class="fa-brands fa-facebook-f text-lg"></i></a>
                    <a href="#" class="text-black hover:text-[#D32F2F] dark:text-white transition-colors"><i class="fa-brands fa-instagram text-lg"></i></a>
                    <a href="#" class="text-black hover:text-[#D32F2F] dark:text-white transition-colors"><i class="fa-brands fa-twitter text-lg"></i></a>
                    <a href="#" class="text-black hover:text-[#D32F2F] dark:text-white transition-colors"><i class="fa-brands fa-youtube text-lg"></i></a>
                </div>
            </div>

            <div>
                <h3 class="text-[19px] font-bold text-black dark:text-white mb-6">Liên kết nhanh</h3>
                <ul class="space-y-3.5">
                    <li><a href="{{ route('app.shop.index') }}" class="text-[15px] font-medium text-black hover:text-[#D32F2F] dark:text-gray-300 transition-colors">Trang chủ</a></li>
                    <li><a href="{{ route('app.products.index') }}" class="text-[15px] font-medium text-black hover:text-[#D32F2F] dark:text-gray-300 transition-colors">Tất cả sản phẩm</a></li>
                    <li><a href="#" class="text-[15px] font-medium text-black hover:text-[#D32F2F] dark:text-gray-300 transition-colors">Sản phẩm mới</a></li>
                    <li><a href="#" class="text-[15px] font-medium text-black hover:text-[#D32F2F] dark:text-gray-300 transition-colors">Ưu đãi đặc biệt</a></li>
                </ul>
            </div>

            <div>
                <h3 class="text-[19px] font-bold text-black dark:text-white mb-6">Hỗ trợ</h3>
                <ul class="space-y-3.5">
                    <li><a href="#" class="text-[15px] font-medium text-black hover:text-[#D32F2F] dark:text-gray-300 transition-colors">Theo dõi đơn hàng</a></li>
                    <li><a href="#" class="text-[15px] font-medium text-black hover:text-[#D32F2F] dark:text-gray-300 transition-colors">Chính sách giao key</a></li>
                    <li><a href="#" class="text-[15px] font-medium text-black hover:text-[#D32F2F] dark:text-gray-300 transition-colors">Hoàn tiền</a></li>
                    <li><a href="#" class="text-[15px] font-medium text-black hover:text-[#D32F2F] dark:text-gray-300 transition-colors">Câu hỏi thường gặp</a></li>
                </ul>
            </div>

            <div>
                <h3 class="text-[19px] font-bold text-black dark:text-white mb-6">Nhận tin mới</h3>
                <p class="text-[15px] font-medium text-black dark:text-gray-300 mb-5">Đăng ký để nhận ưu đãi và sản phẩm mới nhất.</p>
                <form action="#" class="relative w-full">
                    <input type="email" placeholder="Địa chỉ email"
                           class="w-full bg-[#FCEBA7] border-none rounded-md py-3.5 px-4 pr-12 text-[14px] text-black placeholder-black/70 focus:outline-none focus:ring-2 focus:ring-black dark:bg-gray-800 dark:text-white dark:placeholder-gray-400 dark:focus:ring-gray-700">
                    <button type="submit" class="absolute right-0 top-0 bottom-0 px-4 text-black hover:scale-110 transition-transform flex items-center justify-center dark:text-white">
                        <i class="fa-solid fa-arrow-right text-lg"></i>
                    </button>
                </form>
            </div>
        </div>

        <div class="mt-16 pt-8 border-t border-gray-200 dark:border-gray-800 flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-[15px] font-bold text-black dark:text-white">
                &copy; {{ date('Y') }} {{ config('app.name') }}. Đã đăng ký bản quyền.
            </p>
            <div class="flex items-center gap-2">
                <span class="text-[15px] font-bold text-black dark:text-white mr-3 hidden sm:inline-block">Chấp nhận:</span>
                <div class="flex items-center space-x-4 text-black dark:text-white opacity-80">
                    <i class="fa-brands fa-cc-visa text-[32px]"></i>
                    <i class="fa-brands fa-cc-mastercard text-[32px]"></i>
                    <i class="fa-brands fa-cc-paypal text-[32px]"></i>
                    <i class="fa-brands fa-cc-apple-pay text-[32px]"></i>
                </div>
            </div>
        </div>
    </div>
</footer>
