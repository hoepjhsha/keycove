<button
    type="button"
    x-on:click="$wire.toggleRow({{ $row->id }})"
    class="p-1 rounded hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
    x-tooltip="Expand"
>
    <i class="fa-solid fa-chevron-right text-gray-600 dark:text-gray-400" :class="{ 'fa-chevron-down': expandedRows.includes({{ $row->id }}) }"></i>
</button>
