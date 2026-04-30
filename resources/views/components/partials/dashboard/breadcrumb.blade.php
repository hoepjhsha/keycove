@props(['items' => []])

<nav aria-label="Breadcrumb">
    <ol class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
        <li>
            @php
                $homeUrl = '/admin/dashboard';
            @endphp
            <a href="{{ $homeUrl }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                <i class="fa-solid fa-house"></i>
            </a>
        </li>

        @foreach($items as $item)
            <li>
                <i class="fa-solid fa-chevron-right text-[10px] mx-1"></i>
            </li>

            @if($loop->last)
                <li class="font-semibold text-gray-800 dark:text-gray-200 truncate max-w-[150px] sm:max-w-none" aria-current="page">
                    {{ $item['label'] }}
                </li>
            @else
                <li>
                    <a href="{{ $item['url'] ?? '#' }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors whitespace-nowrap">
                        {{ $item['label'] }}
                    </a>
                </li>
            @endif
        @endforeach
    </ol>
</nav>
