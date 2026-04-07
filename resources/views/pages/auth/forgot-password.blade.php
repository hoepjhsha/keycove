<div>
    <div class="text-center mb-8">
        <div class="w-14 h-14 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fa-solid fa-key text-xl"></i>
        </div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Forgot password?</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Enter your email and we'll send you an OTP to reset your password.</p>
    </div>

    <form action="#" method="POST">
        @csrf
        <div class="relative mb-6">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                <i class="fa-regular fa-envelope"></i>
            </div>
            <input type="email" name="email" class="w-full pl-10 pr-3 py-2.5 bg-transparent border border-gray-300 dark:border-gray-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent dark:focus:ring-indigo-400" placeholder="Enter your email" required autofocus>
        </div>

        <button type="submit" class="w-full py-2.5 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition-colors text-sm shadow-md shadow-indigo-500/30">
            Send OTP
        </button>
    </form>

    <div class="mt-8 text-center">
        <a href="#" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors font-medium flex items-center justify-center gap-2">
            <i class="fa-solid fa-arrow-left"></i> Back to log in
        </a>
    </div>
</div>
