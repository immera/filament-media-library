@php
    $items = $getSelectedItems();
    $isDisabled = $isDisabled();
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        class="flex flex-wrap items-center gap-3"
        x-on:media-picker-confirmed.window="
            if ($event.detail.statePath === @js($getStatePath())) {
                $wire.set(@js($getStatePath()), $event.detail.value);
                $wire.unmountAction();
            }
        "
        x-on:media-picker-closed.window="
            if ($event.detail.statePath === @js($getStatePath())) {
                $wire.unmountAction();
            }
        "
    >
        @forelse ($items as $item)
            <div class="flex items-center gap-2 rounded-lg border border-gray-200 p-2 dark:border-gray-700">
                <img
                    src="{{ $item->getFirstMediaUrl('library', 'thumb') }}"
                    alt="{{ $item->alt_text }}"
                    class="h-12 w-12 rounded object-cover"
                />

                @if ($shouldShowFileName())
                    <span class="text-sm text-gray-700 dark:text-gray-200">
                        {{ $item->fileName }}
                    </span>
                @endif

                @if ($isDownloadable())
                    <a
                        href="{{ $item->getFirstMediaUrl('library') }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="text-sm text-primary-600 underline"
                    >
                        {{ __('download') }}
                    </a>
                @endif
            </div>
        @empty
            <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ __('no file selected') }}
            </p>
        @endforelse

        @unless ($isDisabled)
            <div class="flex items-center gap-2">
                {{ $getAction('select') }}

                @if (! empty($items))
                    {{ $getAction('clear') }}
                @endif
            </div>
        @endunless
    </div>
</x-dynamic-component>
