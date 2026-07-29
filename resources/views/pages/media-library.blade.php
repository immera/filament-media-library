<x-filament-panels::page>
    @php
        $breadcrumbs = $this->getBreadcrumbFolders();
        $folders = $this->getFolders();
        $items = $this->getItems();
        $selectedItem = $this->getSelectedItem();
    @endphp

    <div class="flex items-start gap-6">
        <div class="min-w-0 flex-1">
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

                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model.live="sort">
                            <option value="newest">{{ ucfirst(__('newest first')) }}</option>
                            <option value="oldest">{{ ucfirst(__('oldest first')) }}</option>
                            <option value="name_asc">{{ ucfirst(__('name (a-z)')) }}</option>
                            <option value="name_desc">{{ ucfirst(__('name (z-a)')) }}</option>
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>
            </div>

            <div class="mt-6 space-y-8">
                @if ($folders->isNotEmpty())
                    <div class="grid gap-4 grid-cols-[repeat(auto-fill,minmax(135px,135px))]">
                        @foreach ($folders as $folder)
                            <div wire:key="folder-{{ $folder->id }}" class="flex flex-col items-start gap-1">
                                <div class="relative flex aspect-square w-full items-center justify-center rounded-lg bg-gray-50 dark:bg-white/5">
                                    <button
                                        type="button"
                                        wire:click="openFolder({{ $folder->id }})"
                                        class="absolute inset-0 flex items-center justify-center"
                                    >
                                        <x-heroicon-s-folder class="h-14 w-14 text-primary-500" />
                                    </button>

                                    <div class="absolute end-1.5 top-1.5">
                                        <x-filament::dropdown placement="bottom-end">
                                            <x-slot name="trigger">
                                                <x-filament::icon-button
                                                    icon="heroicon-m-ellipsis-vertical"
                                                    :label="ucfirst(__('options'))"
                                                    size="sm"
                                                />
                                            </x-slot>

                                            <x-filament::dropdown.list>
                                                <x-filament::dropdown.list.item
                                                    icon="heroicon-m-pencil-square"
                                                    wire:click="mountAction('editFolder', { folder: {{ $folder->id }} })"
                                                >
                                                    {{ ucfirst(__('rename')) }}
                                                </x-filament::dropdown.list.item>

                                                <x-filament::dropdown.list.item
                                                    icon="heroicon-m-arrows-right-left"
                                                    wire:click="mountAction('moveFolder', { folder: {{ $folder->id }} })"
                                                >
                                                    {{ ucfirst(__('move folder')) }}
                                                </x-filament::dropdown.list.item>

                                                <x-filament::dropdown.list.item
                                                    icon="heroicon-m-trash"
                                                    color="danger"
                                                    wire:click="mountAction('deleteFolder', { folder: {{ $folder->id }} })"
                                                >
                                                    {{ ucfirst(__('delete')) }}
                                                </x-filament::dropdown.list.item>
                                            </x-filament::dropdown.list>
                                        </x-filament::dropdown>
                                    </div>
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
                            </div>
                        @endforeach
                    </div>
                @endif

                @if ($items->isNotEmpty())
                    <div class="grid gap-4 grid-cols-[repeat(auto-fill,minmax(135px,135px))]">
                        @foreach ($items as $item)
                            @php
                                $thumb = $item->getFirstMediaUrl('library', 'thumb');
                                $mimeType = $item->getFirstMedia('library')?->mime_type;
                            @endphp

                            <button
                                type="button"
                                wire:key="item-{{ $item->id }}"
                                wire:click="selectItem({{ $item->id }})"
                                class="flex flex-col items-start gap-1 text-start"
                            >
                                <div
                                    @class([
                                        'flex aspect-square w-full items-center justify-center overflow-hidden rounded-lg bg-gray-50 dark:bg-white/5',
                                        'ring-2 ring-primary-500' => $selectedItem?->id === $item->id,
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
                @endif
            </div>
        </div>

        @if ($selectedItem)
            @php
                $selectedThumb = $selectedItem->getFirstMediaUrl('library', 'thumb');
                $selectedMedia = $selectedItem->getFirstMedia('library');
                $pageCount = $this->getPdfPageCount($selectedItem);
            @endphp

            <aside
                wire:key="item-details-{{ $selectedItem->id }}"
                class="w-full shrink-0 space-y-6 rounded-xl bg-[#27272a] p-6 lg:w-80"
            >
                <div class="relative aspect-square w-full overflow-hidden rounded-lg bg-[#3f3f46]">
                    @if ($selectedThumb)
                        <img
                            src="{{ $selectedThumb }}"
                            alt="{{ $selectedItem->alt_text }}"
                            class="h-full w-full object-contain"
                        />
                    @elseif ($selectedMedia?->mime_type === 'application/pdf')
                        @include('filament-media-library::pages.partials.pdf-preview', ['big' => true])
                    @else
                        <div class="flex h-full w-full items-center justify-center">
                            <x-heroicon-o-document class="h-16 w-16 text-gray-300" />
                        </div>
                    @endif

                    <button
                        type="button"
                        wire:click="mountAction('regenerateItem', { item: {{ $selectedItem->id }} })"
                        title="{{ ucfirst(__('regenerate')) }}"
                        class="absolute end-1.5 top-1.5 flex h-8 w-8 items-center justify-center rounded-full bg-[#3f3f46] text-white hover:opacity-80"
                    >
                        <x-heroicon-m-arrow-path class="h-4 w-4" />
                    </button>

                    <button
                        type="button"
                        wire:click="closeItem"
                        title="{{ ucfirst(__('close')) }}"
                        class="absolute start-1.5 top-1.5 flex h-8 w-8 items-center justify-center rounded-full bg-[#3f3f46] text-white hover:opacity-80"
                    >
                        <x-heroicon-m-x-mark class="h-4 w-4" />
                    </button>
                </div>

                <div>
                    <p class="truncate font-medium text-white">
                        {{ $selectedItem->caption ?: $selectedItem->fileName }}
                    </p>
                    <p class="text-sm text-gray-400">
                        {{ $selectedMedia?->human_readable_size }}
                    </p>
                </div>

                <div>
                    <p class="mb-2 text-sm font-semibold uppercase tracking-wide text-white">
                        {{ __('information') }}
                    </p>

                    <dl class="space-y-2 text-sm">
                        <div class="flex items-center justify-between gap-2">
                            <dt class="text-gray-400">{{ ucfirst(__('uploaded by')) }}</dt>
                            <dd class="font-medium text-white">{{ $selectedItem->user?->name ?? '—' }}</dd>
                        </div>

                        <div class="flex items-center justify-between gap-2">
                            <dt class="text-gray-400">{{ ucfirst(__('uploaded at')) }}</dt>
                            <dd class="font-medium text-white">{{ $selectedItem->created_at?->format('d/m/Y') }}</dd>
                        </div>

                        <div class="flex items-center justify-between gap-2">
                            <dt class="text-gray-400">{{ ucfirst(__('size')) }}</dt>
                            <dd class="font-medium text-white">
                                @if ($pageCount)
                                    {{ $pageCount }} {{ $pageCount === 1 ? __('page') : __('pages') }}
                                @else
                                    {{ $selectedMedia?->human_readable_size }}
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>

                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold uppercase tracking-wide text-white">{{ __('edit') }}</p>
                        <p class="text-sm text-gray-400">
                            {{ ucfirst(__('add information to this file')) }}
                        </p>
                    </div>

                    <button
                        type="button"
                        wire:click="mountAction('editItem', { item: {{ $selectedItem->id }} })"
                        title="{{ ucfirst(__('edit information')) }}"
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#3f3f46] text-white hover:opacity-80"
                    >
                        <x-heroicon-m-pencil-square class="h-4 w-4" />
                    </button>
                </div>

                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold uppercase tracking-wide text-white">{{ __('move file to') }}</p>
                        <p class="truncate text-sm text-gray-400">
                            {{ ucfirst(__('currently in')) }} {{ $selectedItem->folder?->name ?? ucfirst(__('root folder')) }}
                        </p>
                    </div>

                    <button
                        type="button"
                        wire:click="mountAction('moveItem', { item: {{ $selectedItem->id }} })"
                        title="{{ ucfirst(__('move file')) }}"
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#3f3f46] text-white hover:opacity-80"
                    >
                        <x-heroicon-m-arrows-right-left class="h-4 w-4" />
                    </button>
                </div>

                <div>
                    <p class="mb-2 text-sm font-semibold text-white">{{ ucfirst(__('actions')) }}</p>

                    <div class="flex items-center gap-2">
                        <x-filament::button
                            tag="a"
                            href="{{ $selectedItem->getUrl() }}"
                            target="_blank"
                            color="info"
                            class="flex-1 justify-center"
                        >
                            {{ ucfirst(__('view')) }}
                        </x-filament::button>

                        <x-filament::button
                            wire:click="mountAction('deleteItem', { item: {{ $selectedItem->id }} })"
                            class="flex-1 justify-center"
                            style="background-color: #3f3f46 !important; border-color: #3f3f46 !important;"
                        >
                            {{ ucfirst(__('remove')) }}
                        </x-filament::button>
                    </div>
                </div>
            </aside>
        @endif
    </div>
</x-filament-panels::page>
