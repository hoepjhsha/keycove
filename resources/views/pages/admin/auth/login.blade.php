<div>
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Admin Login</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Please enter your credentials to access the admin panel</p>
    </div>

    <form wire:submit="login">
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                <i class="fa-regular fa-envelope"></i>
            </div>
            <input type="email" wire:model="form.email" class="w-full pl-10 pr-3 py-2.5 bg-transparent border border-gray-300 dark:border-gray-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent dark:focus:ring-indigo-400" placeholder="admin@example.com" required autofocus>
        </div>
        <div class="mb-4">
            @error('form.email')
                <small class="text-red-500 block mt-1 font-semibold">{{ $message }}</small>
            @enderror
        </div>

        <div class="relative" x-data="{ show: false }">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                <i class="fa-solid fa-lock"></i>
            </div>
            <input :type="show ? 'text' : 'password'" wire:model="form.password" class="w-full pl-10 pr-10 py-2.5 bg-transparent border border-gray-300 dark:border-gray-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent dark:focus:ring-indigo-400" placeholder="Password" required>
            <button tabindex="-1" type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none">
                <i class="fa-regular" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
            </button>
        </div>
        <div class="mb-4">
            @error('form.password')
                <small class="text-red-500 block mt-1 font-semibold">{{ $message }}</small>
            @enderror
        </div>

        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center">
                <input id="remember" wire:model="form.remember" type="checkbox" class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500 dark:focus:ring-indigo-400 dark:ring-offset-gray-900 focus:ring-2 dark:bg-gray-800 dark:border-gray-700">
                <label for="remember" class="ml-2 text-sm text-gray-700 dark:text-gray-300">Remember me</label>
            </div>
        </div>

        <button type="submit" class="w-full py-2.5 bg-indigo-600 dark:bg-indigo-500 hover:bg-indigo-700 dark:hover:bg-indigo-600 text-white font-semibold rounded-lg transition-colors text-sm">
            Sign in
        </button>
    </form>
</div>
