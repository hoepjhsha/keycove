<div>
    <div class="flex flex-col lg:flex-row min-h-screen w-full bg-[#FCF9F4] dark:bg-gray-950">

        <div class="hidden lg:flex lg:w-1/2 bg-[#F6EBD9] dark:bg-gray-900 relative flex-col pt-32 px-16 lg:px-24 overflow-hidden border-r border-gray-200 dark:border-gray-800">
            <div class="relative z-20">
                <h1 class="text-[42px] font-bold text-black dark:text-white mb-2 tracking-tight">Verify</h1>
                <div class="flex items-center text-[15px] font-medium text-black/70 dark:text-gray-400 space-x-3">
                    <a href="/" class="hover:text-black dark:hover:text-white transition-colors">Home</a>
                    <i class="fa-solid fa-angle-right text-[12px]"></i>
                    <span class="text-black dark:text-white">OTP Verification</span>
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
                    <h2 class="text-3xl font-bold text-black dark:text-white mb-2">Enter OTP</h2>
                    <p class="text-[15px] text-gray-600 dark:text-gray-400">Type the 6-digit code sent to your email.</p>
                </div>

                <form wire:submit="verify" x-data="{
                    otp: @entangle('form.otp_code'),
                    digits: ['', '', '', '', '', ''],
                    remainingTime: @entangle('remainingTime'),
                    timerExpired: @entangle('timerExpired'),
                    timer: null,
                    init() {
                        if (this.otp) { for (let i = 0; i < this.otp.length; i++) { this.digits[i] = this.otp[i]; } }
                        this.$watch('digits', value => { this.otp = value.join(''); });
                        this.startTimer();
                    },
                    startTimer() {
                        if (this.timer) clearInterval(this.timer);
                        this.timer = setInterval(() => {
                            if (this.remainingTime > 0) { this.remainingTime--; }
                            else { this.timerExpired = true; clearInterval(this.timer); }
                        }, 1000);
                    },
                    focusNext(index) { if (index < 5 && this.digits[index]) { this.$refs['input' + (index + 1)].focus(); } },
                    focusPrev(index, event) { if (event.key === 'Backspace' && !this.digits[index] && index > 0) { this.$refs['input' + (index - 1)].focus(); } },
                    handlePaste(event, index) {
                        event.preventDefault();
                        const pasted = (event.clipboardData || window.clipboardData).getData('text');
                        const digits = pasted.replace(/\D/g, '').split('').slice(0, 6);
                        digits.forEach((digit, i) => { if (index + i < 6) { this.digits[index + i] = digit; } });
                        if (digits.length > 0 && index + digits.length < 6) { this.$refs['input' + (index + digits.length)].focus(); }
                    }
                }" class="space-y-6">

                    <div class="text-center p-4 bg-[#FCF9F4] dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Time Remaining</p>
                        <p class="text-3xl font-bold" :class="remainingTime <= 30 ? 'text-[#D32F2F]' : 'text-black dark:text-white'">
                            <span x-text="String(Math.floor(remainingTime / 60)).padStart(2, '0')"></span>:<span x-text="String(remainingTime % 60).padStart(2, '0')"></span>
                        </p>
                    </div>

                    <div class="flex justify-between gap-2">
                        <template x-for="(digit, index) in digits" :key="index">
                            <input type="text" inputmode="numeric" maxlength="1" x-model="digits[index]" :x-ref="'input' + index"
                                   @input="focusNext(index)" @keydown="focusPrev(index, $event)" @paste="handlePaste($event, index)"
                                   class="w-12 h-14 text-center text-xl font-bold bg-transparent border border-black dark:border-gray-600 rounded-lg focus:outline-none focus:ring-1 focus:ring-black dark:focus:ring-white text-black dark:text-white transition-colors"
                                   autofocus>
                        </template>
                    </div>

                    <div>
                        @error('form.otp_code')
                        <small class="text-[#D32F2F] block mt-1 font-bold text-[13px]">{{ $message }}</small>
                        @enderror
                    </div>

                    <button type="submit"
                            :disabled="timerExpired || otp.length !== 6"
                            class="w-full py-3.5 bg-black dark:bg-white text-white dark:text-black font-bold rounded-lg hover:bg-gray-800 transition-colors text-[14px] tracking-wide uppercase disabled:opacity-50 disabled:cursor-not-allowed">
                        Verify Code
                    </button>
                </form>

                <div x-show="timerExpired" class="mt-6 p-5 bg-[#F6EBD9] dark:bg-gray-800 border border-black/10 dark:border-gray-700 rounded-xl">
                    <p class="text-[14px] font-medium text-black dark:text-white mb-4 text-center">
                        The OTP code has expired. Please request a new one.
                    </p>
                    <button wire:click="resend"
                            class="w-full py-3.5 bg-white dark:bg-gray-900 border border-black dark:border-gray-600 text-black dark:text-white font-bold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors text-[14px] tracking-wide uppercase">
                        Resend OTP Code
                    </button>
                </div>

                <p class="text-center text-[15px] font-medium text-gray-600 dark:text-gray-400 mt-8">
                    Didn't receive code? <button wire:click="resend" type="button" class="text-black dark:text-white hover:text-[#D32F2F] dark:hover:text-[#D32F2F] font-bold transition-colors">Resend</button>
                </p>
            </div>
        </div>
    </div>
</div>
