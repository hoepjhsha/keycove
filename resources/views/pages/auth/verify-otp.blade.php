<div>
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Enter OTP</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Type the 6-digit code sent to your email.</p>
    </div>

    <form wire:submit="verify" x-data="{
        otp: @entangle('form.otp_code'),
        digits: ['', '', '', '', '', ''],
        remainingTime: @entangle('remainingTime'),
        timerExpired: @entangle('timerExpired'),
        timer: null,
        init() {
            if (this.otp) {
                for (let i = 0; i < this.otp.length; i++) {
                    this.digits[i] = this.otp[i];
                }
            }
            this.$watch('digits', value => {
                this.otp = value.join('');
            });
            this.startTimer();
        },
        startTimer() {
            if (this.timer) clearInterval(this.timer);
            this.timer = setInterval(() => {
                if (this.remainingTime > 0) {
                    this.remainingTime--;
                } else {
                    this.timerExpired = true;
                    clearInterval(this.timer);
                }
            }, 1000);
        },
        focusNext(index) {
            if (index < 5 && this.digits[index]) {
                this.$refs['input' + (index + 1)].focus();
            }
        },
        focusPrev(index, event) {
            if (event.key === 'Backspace' && !this.digits[index] && index > 0) {
                this.$refs['input' + (index - 1)].focus();
            }
        },
        handlePaste(event, index) {
            event.preventDefault();
            const pasted = (event.clipboardData || window.clipboardData).getData('text');
            const digits = pasted.replace(/\D/g, '').split('').slice(0, 6);

            digits.forEach((digit, i) => {
                if (index + i < 6) {
                    this.digits[index + i] = digit;
                }
            });

            if (digits.length > 0 && index + digits.length < 6) {
                this.$refs['input' + (index + digits.length)].focus();
            }
        }
    }">
        <!-- Timer Display -->
        <div class="text-center mb-6 p-3 bg-gradient-to-r from-indigo-50 to-blue-50 dark:from-indigo-950 dark:to-blue-950 rounded-lg border border-indigo-200 dark:border-indigo-700">
            <p class="text-sm text-gray-600 dark:text-gray-300 mb-1">Time Remaining</p>
            <p class="text-3xl font-bold" :class="remainingTime <= 30 ? 'text-red-600 dark:text-red-400' : 'text-indigo-600 dark:text-indigo-400'">
                <span x-text="String(Math.floor(remainingTime / 60)).padStart(2, '0')"></span>:<span x-text="String(remainingTime % 60).padStart(2, '0')"></span>
            </p>
        </div>

        <div class="flex justify-between gap-2">
            <template x-for="(digit, index) in digits" :key="index">
                <input type="text" inputmode="numeric" maxlength="1" x-model="digits[index]" :x-ref="'input' + index"
                       @input="focusNext(index)" @keydown="focusPrev(index, $event)" @paste="handlePaste($event, index)"
                       class="w-12 h-14 text-center text-xl font-bold bg-transparent border border-gray-300 dark:border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent dark:focus:ring-indigo-400 text-gray-900 dark:text-white"
                       autofocus>
            </template>
        </div>

        <div class="mb-2">
            @error('form.otp_code')
                <small class="text-red-500 block mt-1 font-semibold">{{ $message }}</small>
            @enderror
        </div>

        <button type="submit"
                :disabled="timerExpired || otp.length !== 6"
                class="w-full py-2.5 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition-colors text-sm shadow-md shadow-indigo-500/30 mt-4 disabled:opacity-50 disabled:cursor-not-allowed">
            Verify Code
        </button>
    </form>

    <!-- Expired State -->
    <div x-show="timerExpired" class="mt-6 p-4 bg-yellow-50 dark:bg-yellow-950 border border-yellow-200 dark:border-yellow-700 rounded-lg">
        <p class="text-sm text-yellow-700 dark:text-yellow-300 mb-3">
            The OTP code has expired. Please request a new one.
        </p>
        <button wire:click="resend"
                class="w-full py-2.5 bg-sky-600 text-white font-semibold rounded-lg hover:bg-sky-700 transition-colors text-sm shadow-md shadow-sky-500/30">
            Resend OTP Code
        </button>
    </div>

    <p class="text-center text-sm text-gray-600 dark:text-gray-400 mt-6">
        Didn't receive code? <button wire:click="resend" type="button" class="text-sky-500 hover:underline font-medium">Resend</button>
    </p>
</div>
