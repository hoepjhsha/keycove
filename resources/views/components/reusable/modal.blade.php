@props(['title', 'maxWidth' => '2xl'])

@php
    $maxWidthClass = match ($maxWidth) {
        'sm' => 'sm:max-w-sm',
        'md' => 'sm:max-w-md',
        'lg' => 'sm:max-w-lg',
        'xl' => 'sm:max-w-xl',
        '2xl' => 'sm:max-w-2xl',
        '3xl' => 'sm:max-w-3xl',
        '4xl' => 'sm:max-w-4xl',
        default => 'sm:max-w-2xl',
    };
@endphp

<div
    x-data="{ show: @entangle($attributes->wire('model')) }"
    x-show="show"
    style="display: none;"
    class="fixed inset-0 z-50 overflow-y-auto"
    aria-labelledby="modal-title"
    role="dialog"
    aria-modal="true"
>
    <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">

        <div
            x-show="show"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-sm"
            aria-hidden="true"
        ></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div
            x-show="show"
            @click.outside="show = false"
            @keydown.escape.window="show = false"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="relative inline-block w-full px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white dark:bg-slate-800 rounded-xl shadow-2xl sm:my-8 sm:align-middle {{ $maxWidthClass }} sm:p-6"
        >
            <div class="flex items-start justify-between mb-5 border-b border-slate-200 dark:border-slate-700 pb-3">
                <h3 class="text-xl font-bold text-slate-800 dark:text-white" id="modal-title">
                    {{ $title }}
                </h3>
                <button type="button" @click="show = false" class="text-slate-400 hover:text-slate-500 focus:outline-none">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>

            <div class="mt-2">
                {{ $slot }}
            </div>

            @if (isset($footer))
                <div class="mt-6 flex justify-end gap-3 border-t border-slate-200 dark:border-slate-700 pt-4">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>

{{--<x-modal wire:model="showCreateModal" title="Create New Category" maxWidth="md">--}}

{{--    <div class="text-sm text-slate-500 dark:text-slate-400">--}}
{{--        Nội dung form tạo category (Input Name, Select Parent, v.v...) sẽ nằm ở đây...--}}
{{--    </div>--}}

{{--    <x-slot:footer>--}}
{{--        <button wire:click="$set('showCreateModal', false)" class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg shadow-sm hover:bg-slate-50 transition-colors">--}}
{{--            Cancel--}}
{{--        </button>--}}
{{--        <button wire:click="saveCategory" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow-sm hover:bg-blue-700 transition-colors">--}}
{{--            Save--}}
{{--        </button>--}}
{{--    </x-slot:footer>--}}

{{--</x-modal>--}}
