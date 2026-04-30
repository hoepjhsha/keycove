<div>
    <div class="flex flex-col lg:flex-row min-h-screen w-full bg-[#FCF9F4] dark:bg-gray-950">

        <div class="hidden lg:flex lg:w-1/2 bg-[#F6EBD9] dark:bg-gray-900 relative flex-col pt-32 px-16 lg:px-24 overflow-hidden border-r border-gray-200 dark:border-gray-800">

            <div class="relative z-20">
                <h1 class="text-[42px] font-bold text-black dark:text-white mb-2 tracking-tight">Create Account</h1>
                <div class="flex items-center text-[15px] font-medium text-black/70 dark:text-gray-400 space-x-3">
                    <a href="/" class="hover:text-black dark:hover:text-white transition-colors">Home</a>
                    <i class="fa-solid fa-angle-right text-[12px]"></i>
                    <span class="text-black dark:text-white">Register</span>
                </div>
            </div>

            <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-full max-w-[450px] h-[75%] flex items-end justify-center">
                <div class="absolute bottom-0 w-[85%] h-[85%] bg-white dark:bg-gray-800 rounded-t-[1000px] z-0 shadow-sm"></div>

                <img src="{{ asset('vendor/charactor.png') }}"
                     alt="KeyCove Gaming"
                     class="relative z-10 w-full h-auto scale-[1.5] origin-bottom object-contain drop-shadow-2xl hover:scale-[1.6] transition-transform duration-700" />
            </div>
        </div>

        <div class="w-full lg:w-1/2 flex items-center justify-center p-6 pt-32 sm:p-12 sm:pt-32">

            <div class="w-full max-w-[500px] bg-white dark:bg-gray-900 border border-gray-400 dark:border-gray-700 rounded-[2rem] p-8 sm:p-12 shadow-sm">

                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold text-black dark:text-white mb-2">Register</h2>
                    <p class="text-[15px] text-gray-600 dark:text-gray-400">welcome please create your account</p>
                </div>

                <form wire:submit="register" class="space-y-5">
                    <div>
                        <label class="block text-[15px] font-bold text-black dark:text-white mb-2">Username</label>
                        <input type="text" wire:model="form.username"
                               class="w-full px-4 py-3.5 bg-transparent border border-black dark:border-gray-600 rounded-lg text-[15px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-black dark:focus:ring-white transition-colors"
                               placeholder="Username" required autofocus>
                        @error('form.username')
                        <small class="text-[#D32F2F] block mt-1.5 font-bold text-[13px]">{{ $message }}</small>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-[15px] font-bold text-black dark:text-white mb-2">Email Address</label>
                        <input type="email" wire:model="form.email"
                               class="w-full px-4 py-3.5 bg-transparent border border-black dark:border-gray-600 rounded-lg text-[15px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-black dark:focus:ring-white transition-colors"
                               placeholder="Email Address" required>
                        @error('form.email')
                        <small class="text-[#D32F2F] block mt-1.5 font-bold text-[13px]">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5" x-data="{ show1: false, show2: false }">
                        <div>
                            <label class="block text-[15px] font-bold text-black dark:text-white mb-2">Password</label>
                            <div class="relative">
                                <input :type="show1 ? 'text' : 'password'" wire:model="form.password"
                                       class="w-full pl-4 pr-10 py-3.5 bg-transparent border border-black dark:border-gray-600 rounded-lg text-[15px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-black transition-colors"
                                       placeholder="Password" required>
                                <button tabindex="-1" type="button" @click="show1 = !show1" class="absolute inset-y-0 right-0 pr-3 flex items-center text-black dark:text-gray-400">
                                    <i class="fa-regular" :class="show1 ? 'fa-eye-slash' : 'fa-eye'"></i>
                                </button>
                            </div>
                            @error('form.password')
                            <small class="text-[#D32F2F] block mt-1.5 font-bold text-[13px]">{{ $message }}</small>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-[15px] font-bold text-black dark:text-white mb-2">Confirm</label>
                            <div class="relative">
                                <input :type="show2 ? 'text' : 'password'" wire:model="form.passwordConfirmation"
                                       class="w-full pl-4 pr-10 py-3.5 bg-transparent border border-black dark:border-gray-600 rounded-lg text-[15px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-black transition-colors"
                                       placeholder="Confirm" required>
                                <button tabindex="-1" type="button" @click="show2 = !show2" class="absolute inset-y-0 right-0 pr-3 flex items-center text-black dark:text-gray-400">
                                    <i class="fa-regular" :class="show2 ? 'fa-eye-slash' : 'fa-eye'"></i>
                                </button>
                            </div>
                            @error('form.passwordConfirmation')
                            <small class="text-[#D32F2F] block mt-1.5 font-bold text-[13px]">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="pt-1">
                        <div class="flex items-start">
                            <input id="terms" wire:model="form.terms" type="checkbox"
                                   class="mt-1 w-4 h-4 text-black border-gray-400 rounded focus:ring-black dark:bg-transparent">
                            <label for="terms" class="ml-2.5 text-[14px] text-gray-700 dark:text-gray-300 cursor-pointer leading-relaxed">
                                I agree to the <a href="#" class="text-[#D32F2F] hover:underline font-bold">Terms & Conditions</a>
                            </label>
                        </div>
                        @error('form.terms')
                        <small class="text-[#D32F2F] block mt-1.5 font-bold text-[13px]">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="flex gap-4 pt-4">
                        <button type="submit"
                                class="flex-1 py-3.5 bg-black dark:bg-white text-white dark:text-black font-bold rounded-lg hover:bg-gray-800 dark:hover:bg-gray-100 transition-colors text-[14px] tracking-wide uppercase">
                            Register
                        </button>
                        <a href="{{ route('app.auth.login') }}"
                           class="flex-1 py-3.5 bg-white dark:bg-transparent text-black dark:text-white border border-black dark:border-white font-bold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 flex items-center justify-center transition-colors text-[14px] tracking-wide uppercase">
                            Login
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
