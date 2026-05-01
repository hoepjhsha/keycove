@props(['tableNames' => null, 'tableName' => null])

@php
    // Support both singular and plural forms
    $tables = $tableNames ?? $tableName;
    // Support comma or pipe-separated table names
    $tableArray = is_array($tables)
        ? $tables
        : array_filter(preg_split('/[,|]/', (string) $tables), 'trim');
@endphp

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('swal:confirm', (event) => {
                const data = event[0];
                Swal.fire({
                    title: data.title,
                    text: data.text ?? @js(__('admin.swal.default_confirm_text')),
                    icon: data.type ?? 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: @js(__('admin.common.yes')),
                    cancelButtonText: @js(__('admin.common.cancel'))
                }).then((result) => {
                    if (result.isConfirmed) {
                        Livewire.dispatch(data.method, [data.id]);
                    }
                });
            });

            Livewire.on('swal:success', (event) => {
                Swal.fire({
                    title: @js(__('admin.common.success')),
                    text: event[0].message,
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
                @foreach ($tableArray as $table)
                    Livewire.dispatch('pg:eventRefresh-{{ trim($table) }}');
                @endforeach
            });

            Livewire.on('swal:error', (event) => {
                Swal.fire({
                    title: @js(__('admin.common.error')),
                    text: event[0].message,
                    icon: 'error',
                    timer: 2000,
                    showConfirmButton: false
                });
            });
        });
    </script>
@endpush
