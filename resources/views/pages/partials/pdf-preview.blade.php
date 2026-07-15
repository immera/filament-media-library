@php
    $big ??= false;
@endphp

<div
    class="flex h-full w-full items-center justify-center"
    style="background-image: repeating-conic-gradient(rgba(127, 127, 127, 0.18) 0% 25%, transparent 0% 50%); background-size: {{ $big ? '24px 24px' : '14px 14px' }};"
>
    <div @class(['relative flex flex-col items-center justify-end', 'h-24 w-20' => $big, 'h-12 w-10' => ! $big])>
        <x-heroicon-s-document class="absolute inset-0 h-full w-full text-white drop-shadow-sm dark:text-gray-100" />
        <span @class([
            'relative z-10 rounded-sm bg-gray-950 font-bold tracking-wide text-white',
            'mb-3 px-2 py-1 text-sm' => $big,
            'mb-1 px-1 py-0.5 text-[8px]' => ! $big,
        ])>
            PDF
        </span>
    </div>
</div>
