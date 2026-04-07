<div>
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Create your account</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Join 24000 developers to build amazing apps</p>
    </div>

    <div class="flex gap-4 mb-6">
        <button type="button" class="flex-1 flex items-center justify-center gap-2 py-2.5 px-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors font-medium text-sm">
            <svg class="w-4 h-4" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
            Google
        </button>
        <button type="button" class="flex-1 flex items-center justify-center gap-2 py-2.5 px-4 bg-black dark:bg-white text-white dark:text-black border border-transparent rounded-lg hover:bg-gray-800 dark:hover:bg-gray-100 transition-colors font-medium text-sm">
            <i class="fa-brands fa-github text-base"></i> GitHub
        </button>
    </div>

    <div class="flex items-center mb-6">
        <div class="flex-1 border-t border-gray-200 dark:border-gray-700"></div>
        <span class="px-3 text-sm text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-900">Or sign up with email</span>
        <div class="flex-1 border-t border-gray-200 dark:border-gray-700"></div>
    </div>

    <form action="#" method="POST">
        @csrf
        <div class="relative mb-4">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                <i class="fa-regular fa-user"></i>
            </div>
            <input type="text" name="name" class="w-full pl-10 pr-3 py-2.5 bg-transparent border border-gray-300 dark:border-gray-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent dark:focus:ring-indigo-400" placeholder="Full Name" required>
        </div>

        <div class="relative mb-4">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                <i class="fa-regular fa-envelope"></i>
            </div>
            <input type="email" name="email" class="w-full pl-10 pr-3 py-2.5 bg-transparent border border-gray-300 dark:border-gray-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent dark:focus:ring-indigo-400" placeholder="you@example.com" required>
        </div>

        <div class="relative mb-4" x-data="{ show: false }">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                <i class="fa-solid fa-lock"></i>
            </div>
            <input :type="show ? 'text' : 'password'" name="password" class="w-full pl-10 pr-10 py-2.5 bg-transparent border border-gray-300 dark:border-gray-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent dark:focus:ring-indigo-400" placeholder="Password" required>
            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none">
                <i class="fa-regular" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
            </button>
        </div>

        <div class="relative mb-5" x-data="{ show: false }">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                <i class="fa-solid fa-lock"></i>
            </div>
            <input :type="show ? 'text' : 'password'" name="password_confirmation" class="w-full pl-10 pr-10 py-2.5 bg-transparent border border-gray-300 dark:border-gray-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent dark:focus:ring-indigo-400" placeholder="Confirm Password" required>
            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none">
                <i class="fa-regular" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
            </button>
        </div>

        <div class="flex items-center mb-6">
            <input id="terms" name="terms" type="checkbox" class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500 dark:focus:ring-indigo-400 dark:ring-offset-gray-900 focus:ring-2 dark:bg-gray-800 dark:border-gray-700" required>
            <label for="terms" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                I agree to the <a href="#" class="text-sky-500 hover:underline">Terms & Conditions</a>
            </label>
        </div>

        <button type="submit" class="w-full py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white font-semibold rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors text-sm">
            Sign up
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-gray-600 dark:text-gray-400">
        Already have an account? <a href="#" class="text-sky-500 hover:underline font-medium">Sign in</a>
    </p>
</div>
