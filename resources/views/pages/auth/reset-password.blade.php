<div>
    <div class="text-center mb-8">
        <div class="w-14 h-14 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fa-solid fa-shield-halved text-xl"></i>
        </div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Set new password</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Must be at least 8 characters.</p>
    </div>

    <form wire:submit="resetPassword">
        <div class="relative" x-data="{ show: false }">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                <i class="fa-solid fa-lock"></i>
            </div>
            <input :type="show ? 'text' : 'password'" wire:model="form.password" class="w-full pl-10 pr-10 py-2.5 bg-transparent border border-gray-300 dark:border-gray-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent dark:focus:ring-indigo-400" placeholder="New Password" required autofocus>
            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none">
                <i class="fa-regular" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
            </button>
        </div>
        <div class="mb-4">
            @error('form.password')
                <small class="text-red-500 block mt-1 font-semibold">{{ $message }}</small>
            @enderror
        </div>

        <div class="relative" x-data="{ show: false }">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                <i class="fa-solid fa-lock"></i>
            </div>
            <input :type="show ? 'text' : 'password'" wire:model="form.password_confirmation" class="w-full pl-10 pr-10 py-2.5 bg-transparent border border-gray-300 dark:border-gray-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent dark:focus:ring-indigo-400" placeholder="Confirm New Password" required>
            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none">
                <i class="fa-regular" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
            </button>
        </div>
        <div class="mb-6">
            @error('form.password_confirmation')
                <small class="text-red-500 block mt-1 font-semibold">{{ $message }}</small>
            @enderror
        </div>

        <button type="submit" class="w-full py-2.5 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition-colors text-sm shadow-md shadow-indigo-500/30">
            Reset Password
        </button>
    </form>

    <div class="mt-8 text-center">
        <a href="{{ route('app.auth.login') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors font-medium flex items-center justify-center gap-2">
            <i class="fa-solid fa-arrow-left"></i> Back to log in
        </a>
    </div>
</div>
