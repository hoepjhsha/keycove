<div>
    <div class="flex flex-col lg:flex-row min-h-screen w-full bg-[#FCF9F4] dark:bg-gray-950">

        <div class="hidden lg:flex lg:w-1/2 bg-[#F6EBD9] dark:bg-gray-900 relative flex-col pt-32 px-16 lg:px-24 overflow-hidden border-r border-gray-200 dark:border-gray-800">
            <div class="relative z-20">
                <h1 class="text-[42px] font-bold text-black dark:text-white mb-2 tracking-tight">Khôi phục</h1>
                <div class="flex items-center text-[15px] font-medium text-black/70 dark:text-gray-400 space-x-3">
                    <a href="/" class="hover:text-black dark:hover:text-white transition-colors">Trang chủ</a>
                    <i class="fa-solid fa-angle-right text-[12px]"></i>
                    <span class="text-black dark:text-white">Quên mật khẩu</span>
                </div>
            </div>

            <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-full max-w-[450px] h-[75%] flex items-end justify-center">
                <div class="absolute bottom-0 w-[85%] h-[85%] bg-white dark:bg-gray-800 rounded-t-[1000px] z-0 shadow-sm"></div>
                <img src="{{ asset('vendor/charactor.png') }}" alt="KeyCove Gaming" class="relative z-10 w-full h-auto scale-[1.5] origin-bottom object-contain drop-shadow-2xl hover:scale-[1.6] transition-transform duration-700" />
            </div>
        </div>

        <div class="w-full lg:w-1/2 flex items-center justify-center p-6 pt-28 sm:p-12 sm:pt-32">
            <div class="w-full max-w-[500px] bg-white dark:bg-gray-900 border border-gray-400 dark:border-gray-700 rounded-[2rem] p-8 sm:p-12">

                <div class="text-center mb-8">
                    <div class="w-14 h-14 bg-[#FCF9F4] dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-black dark:text-white rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fa-solid fa-key text-xl"></i>
                    </div>
                    <h2 class="text-3xl font-bold text-black dark:text-white mb-2">Quên mật khẩu?</h2>
                    <p class="text-[15px] text-gray-600 dark:text-gray-400">Nhập email để nhận mã OTP đặt lại mật khẩu.</p>
                </div>

                <form wire:submit="sendOtp" class="space-y-6">
                    <div>
                        <label class="block text-[15px] font-bold text-black dark:text-white mb-2">Địa chỉ email</label>
                        <input type="email" wire:model="form.email"
                               class="w-full px-4 py-3.5 bg-transparent border border-black dark:border-gray-600 rounded-lg text-[15px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-black dark:focus:ring-white transition-colors"
                                placeholder="Nhập email của bạn" required autofocus>
                        @error('form.email')
                        <small class="text-[#D32F2F] block mt-1.5 font-bold text-[13px]">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="pt-2">
                        <button type="submit"
                                class="w-full py-3.5 bg-black dark:bg-white text-white dark:text-black font-bold rounded-lg hover:bg-[#D32F2F] dark:hover:bg-[#D32F2F] dark:hover:text-white transition-colors text-[14px] tracking-wide uppercase">
                            Gửi mã OTP
                        </button>
                    </div>
                </form>

                <div class="mt-8 text-center">
                    <a href="{{ route('app.auth.login') }}" class="text-[14px] text-gray-500 dark:text-gray-400 hover:text-black dark:hover:text-white transition-colors font-bold flex items-center justify-center gap-2">
                        <i class="fa-solid fa-arrow-left"></i> Quay lại đăng nhập
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
