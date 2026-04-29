<div>
    <div class="flex flex-col lg:flex-row min-h-screen w-full bg-[#FCF9F4] dark:bg-gray-950">

        <div class="hidden lg:flex lg:w-1/2 bg-[#F6EBD9] dark:bg-gray-900 relative flex-col pt-32 px-16 lg:px-24 overflow-hidden border-r border-gray-200 dark:border-gray-800">
            <div class="relative z-20">
                <h1 class="text-[42px] font-bold text-black dark:text-white mb-2 tracking-tight">Success</h1>
                <div class="flex items-center text-[15px] font-medium text-black/70 dark:text-gray-400 space-x-3">
                    <a href="/" class="hover:text-black dark:hover:text-white transition-colors">Home</a>
                    <i class="fa-solid fa-angle-right text-[12px]"></i>
                    <span class="text-black dark:text-white">Verified</span>
                </div>
            </div>

            <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-full max-w-[450px] h-[75%] flex items-end justify-center">
                <div class="absolute bottom-0 w-[85%] h-[85%] bg-white dark:bg-gray-800 rounded-t-[1000px] z-0 shadow-sm"></div>
                <img src="{{ asset('vendor/charactor.png') }}" alt="KeyCove Gaming" class="relative z-10 w-full h-auto scale-[1.5] origin-bottom object-contain drop-shadow-2xl hover:scale-[1.6] transition-transform duration-700" />
            </div>
        </div>

        <div class="w-full lg:w-1/2 flex items-center justify-center p-6 pt-28 sm:p-12 sm:pt-32">
            <div class="w-full max-w-[500px] bg-white dark:bg-gray-900 border border-gray-400 dark:border-gray-700 rounded-[2rem] p-8 sm:p-12 text-center">

                <div class="w-20 h-20 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-600 dark:text-green-400 rounded-full flex items-center justify-center mx-auto mb-8 shadow-sm">
                    <i class="fa-solid fa-check text-4xl"></i>
                </div>

                <h2 class="text-3xl font-bold text-black dark:text-white mb-4">Email Verified!</h2>

                <p class="text-[15px] text-gray-600 dark:text-gray-400 mb-10 leading-relaxed">
                    Your email address has been successfully verified.<br>
                    Thank you for confirming your account.
                </p>

                <a href="{{ route('app.shop.index') }}"
                   class="flex justify-center items-center w-full py-4 bg-black dark:bg-white text-white dark:text-black font-bold rounded-lg hover:bg-[#D32F2F] dark:hover:bg-[#D32F2F] dark:hover:text-white transition-colors text-[14px] tracking-wide uppercase">
                    Continue to App
                </a>
            </div>
        </div>
    </div>
</div>
