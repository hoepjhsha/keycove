@if ($paginator->hasPages())
    <div class="rounded-md border border-gray-200 bg-white px-4 py-3 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-wrap items-center gap-2">
                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}
                </span>
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    {{ number_format($paginator->total()) }} results
                </span>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button type="button"
                        wire:click="gotoPage(1, '{{ $paginator->getPageName() }}')"
                        wire:loading.attr="disabled"
                        @disabled($paginator->onFirstPage())
                        class="inline-flex items-center gap-2 rounded-md border border-gray-200 bg-white px-3 py-2 text-xs font-medium text-gray-700 transition-colors hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-200 dark:hover:bg-gray-900">
                    First
                </button>

                <button type="button"
                        wire:click="previousPage('{{ $paginator->getPageName() }}')"
                        wire:loading.attr="disabled"
                        @disabled($paginator->onFirstPage())
                        class="inline-flex items-center gap-2 rounded-md border border-gray-200 bg-white px-3 py-2 text-xs font-medium text-gray-700 transition-colors hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-200 dark:hover:bg-gray-900">
                    Previous
                </button>

                <button type="button"
                        wire:click="nextPage('{{ $paginator->getPageName() }}')"
                        wire:loading.attr="disabled"
                        @disabled($paginator->hasMorePages() === false)
                        class="inline-flex items-center gap-2 rounded-md border border-gray-200 bg-white px-3 py-2 text-xs font-medium text-gray-700 transition-colors hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-200 dark:hover:bg-gray-900">
                    Next
                </button>

                <button type="button"
                        wire:click="gotoPage({{ $paginator->lastPage() }}, '{{ $paginator->getPageName() }}')"
                        wire:loading.attr="disabled"
                        @disabled($paginator->onLastPage())
                        class="inline-flex items-center gap-2 rounded-md border border-gray-200 bg-white px-3 py-2 text-xs font-medium text-gray-700 transition-colors hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-200 dark:hover:bg-gray-900">
                    Last
                </button>

                <div class="flex items-center gap-2 rounded-md border border-gray-200 bg-gray-50 px-3 py-2 dark:border-gray-700 dark:bg-gray-950">
                    <label for="go-to-page" class="text-xs font-medium text-gray-500 dark:text-gray-400">Go to</label>
                    <input id="go-to-page"
                           type="number"
                           min="1"
                           max="{{ $paginator->lastPage() }}"
                           value="{{ $paginator->currentPage() }}"
                           inputmode="numeric"
                           class="w-20 rounded-md border border-gray-200 bg-white px-2 py-1 text-xs text-gray-700 focus:border-indigo-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                           x-data="{ page: @js($paginator->currentPage()) }"
                           x-on:change="$wire.gotoPage(Math.min(Math.max(parseInt($event.target.value || 1, 10), 1), {{ $paginator->lastPage() }}), '{{ $paginator->getPageName() }}')">
                    <span class="text-xs text-gray-500 dark:text-gray-400">/ {{ $paginator->lastPage() }}</span>
                </div>
            </div>
        </div>
    </div>
@endif
