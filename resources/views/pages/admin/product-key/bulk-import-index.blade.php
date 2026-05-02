<div>
    @section('pageTitle', __('admin.titles.bulk_import_keys'))

    @push('breadcrumbs')
        <x-partials.dashboard.breadcrumb :items="[
            ['label' => __('admin.nav.catalog_keys'), 'url' => 'javascript:void(0)'],
            ['label' => __('admin.nav.bulk_import_keys'), 'url' => 'javascript:void(0)'],
        ]" />
    @endpush

    <div class="bg-white dark:bg-slate-800 shadow rounded-md w-full relative">
        <div class="border-b border-dashed border-slate-200 dark:border-slate-700 py-3 px-4 dark:text-slate-300/70">
            <div class="flex items-center justify-between">
                <h4 class="font-medium">{{ __('admin.nav.bulk_import_keys') }}</h4>
                <button wire:click="downloadTemplate" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                    <i class="fa-solid fa-download mr-1"></i> {{ __('admin.common.download_template') }}
                </button>
            </div>
        </div>

        <div class="p-6">
            @if (!$importResult)
                <div class="text-center py-8">
                    <div class="mb-4">
                        <i class="fa-solid fa-file-import text-4xl text-slate-400 dark:text-slate-500"></i>
                    </div>
                    <p class="text-slate-600 dark:text-slate-400 mb-4">{{ __('admin.common.upload_keys_help') }}</p>
                    <p class="text-sm text-slate-500 dark:text-slate-500 mb-6">{{ __('admin.common.bulk_import_file_requirements') }}</p>

                    <div class="max-w-md mx-auto">
                        <label class="block">
                            <span class="sr-only">{{ __('admin.common.choose_file') }}</span>
                            <input type="file"
                                   wire:model="importForm.file"
                                   accept=".csv,.xlsx"
                                   class="block w-full text-sm text-slate-500
                                          file:mr-4 file:py-2 file:px-4
                                          file:rounded-full file:border-0
                                          file:text-sm file:font-semibold
                                          file:bg-blue-50 file:text-blue-700
                                          hover:file:bg-blue-100
                                          dark:file:bg-slate-700 dark:file:text-blue-400
                                          dark:text-slate-400
                                          cursor-pointer"
                            />
                        </label>
                        @error('importForm.file')
                            <small class="error block text-red-500 text-xs mt-2">{{ $message }}</small>
                        @enderror
                    </div>

                    @if ($importForm->file)
                        <div class="mt-4 p-3 bg-blue-50 dark:bg-slate-700 rounded-lg inline-block">
                            <p class="text-sm text-blue-700 dark:text-blue-300">
                                <i class="fa-solid fa-file mr-2"></i>
                                {{ $importForm->file->getClientOriginalName() }}
                                ({{ number_format($importForm->file->getSize() / 1024, 2) }} KB)
                            </p>
                        </div>
                    @endif
                </div>

                <div class="flex justify-center mt-4">
                    <button wire:click="processImport"
                            wire:loading.attr="disabled"
                            @if (!$importForm->file) disabled @endif
                            class="inline-block focus:outline-none text-white hover:bg-blue-600 bg-blue-500 border border-blue-500 dark:bg-blue-600 dark:border-blue-600 dark:hover:bg-blue-700 text-sm font-medium py-2 px-4 rounded transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="processImport">
                            <i class="fa-solid fa-upload mr-2"></i> {{ __('admin.common.import_keys') }}
                        </span>
                        <span wire:loading wire:target="processImport">
                            <i class="fa-solid fa-circle-notch fa-spin mr-2"></i> {{ __('admin.common.processing') }}
                        </span>
                    </button>
                </div>
            @else
                <div class="space-y-6">
                    <div class="flex items-center justify-between">
                        <h5 class="font-medium text-lg">{{ __('admin.common.import_results') }}</h5>
                        <button wire:click="resetImport" class="text-sm text-blue-600 hover:text-blue-800">
                            <i class="fa-solid fa-plus mr-1"></i> {{ __('admin.common.import_more') }}
                        </button>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div class="bg-slate-50 dark:bg-slate-700 p-4 rounded-lg text-center">
                            <p class="text-2xl font-bold text-slate-700 dark:text-slate-200">{{ $importResult['totalRows'] }}</p>
                            <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('admin.common.total_rows') }}</p>
                        </div>
                        <div class="bg-green-50 dark:bg-green-900/30 p-4 rounded-lg text-center">
                            <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $importResult['successCount'] }}</p>
                            <p class="text-sm text-green-600 dark:text-green-400">{{ __('admin.common.successful') }}</p>
                        </div>
                        <div class="bg-red-50 dark:bg-red-900/30 p-4 rounded-lg text-center">
                            <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $importResult['failureCount'] }}</p>
                            <p class="text-sm text-red-600 dark:text-red-400">{{ __('admin.common.failed') }}</p>
                        </div>
                    </div>

                    @if (!empty($importResult['errors']))
                        <div class="border border-red-200 dark:border-red-800 rounded-lg overflow-hidden">
                            <div class="bg-red-50 dark:bg-red-900/20 px-4 py-3 border-b border-red-200 dark:border-red-800">
                                <h6 class="font-medium text-red-800 dark:text-red-300">{{ __('admin.common.validation_errors') }}</h6>
                            </div>
                            <div class="max-h-64 overflow-y-auto">
                                <table class="w-full text-sm">
                                    <thead class="bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                                        <tr>
                                            <th class="px-4 py-2 text-left">{{ __('admin.common.row') }}</th>
                                            <th class="px-4 py-2 text-left">{{ __('admin.common.field') }}</th>
                                            <th class="px-4 py-2 text-left">{{ __('admin.common.error') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                        @foreach($importResult['errors'] as $error)
                                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800">
                                                <td class="px-4 py-2 text-slate-700 dark:text-slate-300">{{ $error['rowNumber'] }}</td>
                                                <td class="px-4 py-2 text-slate-700 dark:text-slate-300">{{ $error['field'] }}</td>
                                                <td class="px-4 py-2 text-red-600 dark:text-red-400">{{ $error['message'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="flex justify-center">
                            <button wire:click="downloadErrorReport" class="inline-block focus:outline-none text-red-600 hover:bg-red-600 hover:text-white bg-transparent border border-red-300 dark:border-red-700 text-sm font-medium py-2 px-4 rounded transition-colors">
                                <i class="fa-solid fa-download mr-2"></i> {{ __('admin.common.download_error_report') }}
                            </button>
                        </div>
                    @endif

                    @if ($importResult['successCount'] > 0)
                        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                            <p class="text-green-700 dark:text-green-300">
                                <i class="fa-solid fa-check-circle mr-2"></i>
                                {{ __('admin.messages.import_success_count', ['count' => $importResult['successCount']]) }}
                            </p>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
