@props(['status'])

@php
    $color = match ($status) {
        '予定あり' => 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-100',
        '使用済み' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100',
        default => 'bg-gray-200 text-gray-700 dark:bg-gray-600 dark:text-gray-100',
    };
@endphp

<span class="rounded px-2 py-0.5 text-xs {{ $color }}">{{ $status }}</span>
