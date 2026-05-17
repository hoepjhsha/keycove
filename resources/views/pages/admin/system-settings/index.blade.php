<div>
    @section('pageTitle', __('admin.titles.manage_system_settings'))

    @push('breadcrumbs')
        <x-partials.dashboard.breadcrumb :items="[
            ['label' => __('admin.nav.system_settings'), 'url' => 'javascript:void(0)'],
        ]" />
    @endpush

    <div class="bg-white dark:bg-slate-800 shadow rounded-2xl border border-slate-200 dark:border-slate-700 overflow-visible">
        <div class="flex flex-col gap-4 border-b border-slate-200 dark:border-slate-700 p-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h4 class="text-base font-semibold text-slate-900 dark:text-slate-100">{{ __('admin.system_settings.heading') }}</h4>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('admin.system_settings.hint') }}</p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <label class="relative block">
                    <span class="sr-only">{{ __('admin.placeholders.search_settings') }}</span>
                    <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                    <input wire:model.live.debounce.300ms="search" type="text" class="w-full sm:w-72 rounded-xl border border-slate-300 bg-white pl-9 pr-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/15 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100" placeholder="{{ __('admin.placeholders.search_settings') }}">
                </label>
            </div>
        </div>

        <div class="divide-y divide-slate-200 dark:divide-slate-700">
            @forelse($this->configs as $config)
                <div wire:key="system-config-{{ $config->id }}" class="flex flex-col gap-4 p-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="min-w-0 flex-1">
                        <div class="group relative inline-flex max-w-full">
                            <div class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 dark:bg-slate-700 dark:text-slate-200">
                                <i class="fa-solid fa-gear text-[11px] text-slate-400"></i>
                                <span class="font-mono truncate">{{ $config->key }}</span>
                                <i class="fa-regular fa-circle-question text-slate-400"></i>
                            </div>

                            <div class="pointer-events-none absolute left-0 top-full z-20 mt-2 hidden w-80 rounded-2xl border border-slate-200 bg-slate-950 px-3 py-2 text-xs text-slate-100 shadow-xl group-hover:block dark:border-slate-700">
                                <div class="font-semibold text-white">{{ __('admin.common.description') }}</div>
                                <div class="mt-1 leading-5 text-slate-300">{{ $config->description ?: __('admin.system_settings.description_fallback') }}</div>
                            </div>
                        </div>

                        <div class="mt-2 text-sm text-slate-500 dark:text-slate-400">{{ __('admin.system_settings.hover_hint') }}</div>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center lg:w-[52%]">
                        <div class="flex-1">
                            <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('admin.common.value') }}</label>
                            <input wire:model.live.debounce.500ms="inlineValues.{{ $config->id }}" type="text" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm transition-colors focus:border-primary-500 focus:ring-2 focus:ring-primary-500/15 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100" />

                            @if($this->isJsonValue($config->value))
                                <div class="mt-2 rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-900/70">
                                    <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                        <span class="rounded-full bg-slate-200 px-2 py-0.5 text-[10px] text-slate-700 dark:bg-slate-700 dark:text-slate-200">JSON</span>
                                        {{ __('admin.system_settings.pretty_preview') }}
                                    </div>
                                    <pre class="overflow-x-auto font-mono text-xs leading-5 text-slate-700 whitespace-pre dark:text-slate-200">{{ $this->prettyJson($config->value) }}</pre>
                                </div>
                            @endif
                        </div>

                        <div class="flex items-center gap-2 self-end sm:self-auto">
                            <button wire:click="saveInlineValue({{ $config->id }})" type="button" class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-slate-700 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-white">
                                {{ __('admin.common.save') }}
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-4 py-16 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-700">
                        <i class="fa-solid fa-sliders text-xl"></i>
                    </div>
                    <h3 class="mt-4 text-sm font-semibold text-slate-900 dark:text-slate-100">{{ __('admin.system_settings.empty_title') }}</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('admin.system_settings.empty_description') }}</p>
                </div>
            @endforelse
        </div>
    </div>

    <x-reusable.modal wire:model="showViewModal" :title="__('admin.modal.system_setting_details')" max-width="2xl">
        @if($viewData)
            <div class="space-y-4">
                <div class="bg-slate-50 dark:bg-slate-900 rounded-lg p-4 text-sm border border-slate-200 dark:border-slate-700">
                    <dl class="divide-y divide-slate-200 dark:divide-slate-700">
                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">{{ __('admin.common.id') }}</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white font-semibold">#{{ $viewData['id'] }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">{{ __('admin.common.key') }}</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white">{{ $viewData['key'] }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">{{ __('admin.common.value') }}</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white break-words">
                                @if($this->isJsonValue($viewData['value']))
                                    <pre class="overflow-x-auto rounded-xl border border-slate-200 bg-slate-950 p-3 font-mono text-xs leading-5 text-slate-100 dark:border-slate-700">{{ $this->prettyJson($viewData['value']) }}</pre>
                                @else
                                    {{ $viewData['value'] }}
                                @endif
                            </dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 py-3 border-b-0">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">{{ __('admin.common.description') }}</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white">{{ $viewData['description'] ?? __('admin.common.none') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        @endif

        <x-slot:footer>
            <button wire:click="$set('showViewModal', false)" class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg shadow-sm hover:bg-slate-50 transition-colors inline-flex items-center">
                <i class="fa-solid fa-xmark mr-2"></i> {{ __('admin.common.close') }}
            </button>
        </x-slot:footer>
    </x-reusable.modal>

</div>
