<div>
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Enter OTP</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Type the 6-digit code sent to your email.</p>
    </div>

    <form action="#" method="POST" x-data="{ otp: ['', '', '', '', '', ''] }" @submit.prevent="$el.submit()">
        @csrf
        <input type="hidden" name="otp_code" :value="otp.join('')">

        <div class="flex justify-between gap-2 mb-8" x-data="{
                    focusNext(index) {
                        if (index < 5 && this.otp[index]) {
                            this.$refs['input' + (index + 1)].focus();
                        }
                    },
                    focusPrev(index, event) {
                        if (event.key === 'Backspace' && !this.otp[index] && index > 0) {
                            this.$refs['input' + (index - 1)].focus();
                        }
                    }
                }">
            <template x-for="(digit, index) in otp" :key="index">
                <input type="text" maxlength="1" x-model="otp[index]" :x-ref="'input' + index"
                       @input="focusNext(index)" @keydown="focusPrev(index, $event)"
                       class="w-12 h-14 text-center text-xl font-bold bg-transparent border border-gray-300 dark:border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent dark:focus:ring-indigo-400 text-gray-900 dark:text-white"
                       autofocus>
            </template>
        </div>

        <button type="submit" class="w-full py-2.5 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition-colors text-sm shadow-md shadow-indigo-500/30">
            Verify Code
        </button>
    </form>

    <p class="text-center text-sm text-gray-600 dark:text-gray-400 mt-6">
        Didn't receive code? <a href="#" class="text-sky-500 hover:underline font-medium">Resend</a>
    </p>
</div>
