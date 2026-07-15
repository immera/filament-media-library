<div>
    @php
        $breadcrumbs = $this->getBreadcrumbFolders();
        $folders = $this->getFolders();
        $items = $this->getItems();
    @endphp

    <div class="flex flex-wrap items-center justify-between gap-3">
        <nav class="flex flex-wrap items-center gap-x-1 gap-y-1 text-sm">
            <button
                type="button"
                wire:click="openFolder(null)"
                @class([
                    'flex items-center gap-1 rounded-md px-2 py-1 font-medium hover:bg-gray-100 dark:hover:bg-white/5',
                    'text-gray-950 dark:text-white' => $breadcrumbs->isEmpty(),
                    'text-gray-500 dark:text-gray-400' => $breadcrumbs->isNotEmpty(),
                ])
            >
                <x-heroicon-o-home class="h-4 w-4" />
                {{ ucfirst(__('media')) }}
            </button>

            @foreach ($breadcrumbs as $crumb)
                <x-heroicon-m-chevron-right class="h-4 w-4 shrink-0 text-gray-400" />

                <button
                    type="button"
                    wire:click="openFolder({{ $crumb->id }})"
                    @class([
                        'rounded-md px-2 py-1 font-medium hover:bg-gray-100 dark:hover:bg-white/5',
                        'text-gray-950 dark:text-white' => $loop->last,
                        'text-gray-500 dark:text-gray-400' => ! $loop->last,
                    ])
                >
                    {{ $crumb->name }}
                </button>
            @endforeach
        </nav>

        <div class="flex items-center gap-2">
            <div class="w-48">
                <x-filament::input.wrapper>
                    <x-filament::input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        :placeholder="__('Enter a term to search')"
                    />
                </x-filament::input.wrapper>
            </div>

            <x-filament::button
                type="button"
                color="gray"
                icon="heroicon-o-folder-plus"
                wire:click="mountAction('newFolder')"
            >
                {{ ucfirst(__('new folder')) }}
            </x-filament::button>

            <x-filament::button
                type="button"
                icon="heroicon-o-arrow-up-tray"
                wire:click="mountAction('upload')"
            >
                {{ ucfirst(__('upload')) }}
            </x-filament::button>
        </div>
    </div>

    <div class="mt-4 max-h-[28rem] space-y-6 overflow-y-auto">
        @if ($folders->isNotEmpty())
            <div class="grid gap-4 grid-cols-[repeat(auto-fill,minmax(135px,135px))]">
                @foreach ($folders as $folder)
                    <button
                        type="button"
                        wire:key="folder-{{ $folder->id }}"
                        wire:click="openFolder({{ $folder->id }})"
                        class="flex flex-col items-start gap-1 text-start"
                    >
                        <div class="flex aspect-square w-full items-center justify-center rounded-lg bg-gray-50 dark:bg-white/5">
                            <x-heroicon-s-folder class="h-14 w-14 text-primary-500" />
                        </div>

                        <p class="w-full truncate text-sm font-medium text-gray-950 dark:text-white">
                            {{ $folder->name }}
                        </p>

                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            @if (($folder->children_count + $folder->items_count) === 0)
                                {{ ucfirst(__('this folder is empty')) }}
                            @else
                                {{ $folder->children_count + $folder->items_count }} {{ __('items') }}
                            @endif
                        </p>
                    </button>
                @endforeach
            </div>
        @endif

        @if ($items->isNotEmpty())
            <div class="grid gap-4 grid-cols-[repeat(auto-fill,minmax(135px,135px))]">
                @foreach ($items as $item)
                    @php
                        $thumb = $item->getFirstMediaUrl('library', 'thumb');
                        $mimeType = $item->getFirstMedia('library')?->mime_type;
                        $isSelected = in_array($item->id, $selected, true);
                    @endphp

                    <button
                        type="button"
                        wire:key="item-{{ $item->id }}"
                        wire:click="toggleSelect({{ $item->id }})"
                        class="flex flex-col items-start gap-1 text-start"
                    >
                        <div
                            @class([
                                'relative flex aspect-square w-full items-center justify-center overflow-hidden rounded-lg bg-gray-50 dark:bg-white/5',
                                'ring-2 ring-primary-500' => $isSelected,
                            ])
                        >
                            @if ($thumb)
                                <img
                                    src="{{ $thumb }}"
                                    alt="{{ $item->alt_text }}"
                                    class="h-full w-full object-cover"
                                />
                            @elseif ($mimeType === 'application/pdf')
                                @include('filament-media-library::pages.partials.pdf-preview', ['big' => false])
                            @else
                                <x-heroicon-o-document class="h-10 w-10 text-gray-400" />
                            @endif

                            @if ($isSelected)
                                <div class="absolute end-1.5 top-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-primary-500 text-white">
                                    <x-heroicon-m-check class="h-3.5 w-3.5" />
                                </div>
                            @endif
                        </div>

                        <p class="w-full truncate text-sm font-medium text-gray-950 dark:text-white">
                            {{ $item->caption ?: $item->fileName }}
                        </p>

                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $item->getFirstMedia('library')?->human_readable_size }}
                        </p>
                    </button>
                @endforeach
            </div>
        @endif

        @if ($folders->isEmpty() && $items->isEmpty())
            <div class="flex flex-col items-center justify-center gap-2 rounded-xl border border-dashed border-gray-300 py-16 dark:border-white/10">
                <x-heroicon-o-photo class="h-12 w-12 text-gray-400" />
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ ucfirst(__('this folder is empty')) }}
                </p>
            </div>
        @elseif ($folders->isNotEmpty() && $items->isEmpty() && filled($acceptedFileTypes))
            <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ ucfirst(__('no matching files in this folder, check the folders above')) }}
            </p>
        @endif
    </div>

    <div class="mt-4 flex items-center justify-between border-t border-gray-200 pt-4 dark:border-white/10">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            {{ count($selected) }} {{ __('selected') }}
        </p>

        <div class="flex items-center gap-2">
            <x-filament::button type="button" color="gray" wire:click="close">
                {{ ucfirst(__('close')) }}
            </x-filament::button>

            <x-filament::button type="button" wire:click="confirm">
                {{ ucfirst(__('update')) }}
            </x-filament::button>
        </div>
    </div>

    <x-filament-actions::modals />
</div>
