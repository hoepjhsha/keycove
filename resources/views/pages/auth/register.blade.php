<div>
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Create your account</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Join 24000 developers to build amazing apps</p>
    </div>

    <form wire:submit="register">
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                <i class="fa-regular fa-user"></i>
            </div>
            <input type="text" wire:model="form.username" class="w-full pl-10 pr-3 py-2.5 bg-transparent border border-gray-300 dark:border-gray-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent dark:focus:ring-indigo-400" placeholder="Username" required autofocus>
        </div>
        <div class="mb-4">
            @error('form.username')
                <small class="text-red-500 block mt-1 font-semibold">{{ $message }}</small>
            @enderror
        </div>

        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                <i class="fa-regular fa-envelope"></i>
            </div>
            <input type="email" wire:model="form.email" class="w-full pl-10 pr-3 py-2.5 bg-transparent border border-gray-300 dark:border-gray-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent dark:focus:ring-indigo-400" placeholder="you@example.com" required>
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

        <div class="relative" x-data="{ show: false }">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                <i class="fa-solid fa-lock"></i>
            </div>
            <input :type="show ? 'text' : 'password'" wire:model="form.passwordConfirmation" class="w-full pl-10 pr-10 py-2.5 bg-transparent border border-gray-300 dark:border-gray-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent dark:focus:ring-indigo-400" placeholder="Confirm Password" required>
            <button tabindex="-1" type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none">
                <i class="fa-regular" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
            </button>
        </div>
        <div class="mb-5">
            @error('form.passwordConfirmation')
                <small class="text-red-500 block mt-1 font-semibold">{{ $message }}</small>
            @enderror
        </div>

        <div class="flex flex-col">
            <div class="flex items-center">
                <input id="terms" wire:model="form.terms" type="checkbox" class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500 dark:focus:ring-indigo-400 dark:ring-offset-gray-900 focus:ring-2 dark:bg-gray-800 dark:border-gray-700">
                <label for="terms" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                    I agree to the <a href="#" class="text-sky-500 hover:underline">Terms & Conditions</a>
                </label>
            </div>
        </div>
        <div class="mb-6">
            @error('form.terms')
                <small class="text-red-500 block mt-1 font-semibold">{{ $message }}</small>
            @enderror
        </div>

        <button type="submit" class="w-full py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white font-semibold rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors text-sm">
            Sign up
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-gray-600 dark:text-gray-400">
        Already have an account? <a href="{{ route('app.auth.login') }}" class="text-sky-500 hover:underline font-medium">Sign in</a>
    </p>
</div>
