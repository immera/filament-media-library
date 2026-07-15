<?php

namespace Immera\FilamentMediaLibrary\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieTagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Panel;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Collection;
use Immera\FilamentMediaLibrary\Models\MediaLibraryFolder;
use Immera\FilamentMediaLibrary\Models\MediaLibraryItem;
use Livewire\Attributes\Url;
use Spatie\MediaLibrary\Conversions\FileManipulator;
use Spatie\PdfToImage\Pdf as PdfToImage;
use Throwable;

class MediaLibrary extends Page
{
    protected string $view = 'filament-media-library::pages.media-library';

    #[Url(as: 'folder')]
    public ?int $folderId = null;

    public ?int $selectedItemId = null;
    public string $search = '';
    public string $sort = 'newest';

    public static function getSlug(?Panel $panel = null): string
    {
        return 'media-browser';
    }

    public static function getNavigationGroup(): ?string
    {
        return ucfirst(__('content'));
    }

    public static function getNavigationLabel(): string
    {
        return ucfirst(__('media'));
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-photo';
    }

    public static function getNavigationSort(): int
    {
        return 0;
    }

    public function getTitle(): string|Htmlable
    {
        return ucfirst(__('media'));
    }

    public function mount(): void
    {
        if ($this->folderId && ! MediaLibraryFolder::query()->whereKey($this->folderId)->exists()) {
            $this->folderId = null;
        }
    }

    public function openFolder(?int $folderId): void
    {
        $this->folderId = $folderId;
        $this->selectedItemId = null;
    }

    public function selectItem(?int $itemId): void
    {
        $this->selectedItemId = $itemId;
    }

    public function closeItem(): void
    {
        $this->selectedItemId = null;
    }

    public function acceptImage(): bool
    {
        return true;
    }

    public function acceptVideo(): bool
    {
        return true;
    }

    public function acceptAudio(): bool
    {
        return true;
    }

    public function acceptDocument(): bool
    {
        return true;
    }

    /**
     * @return array<string>
     */
    public function getAcceptedFileTypes(): array
    {
        $types = [];

        if ($this->acceptImage()) {
            $types[] = 'image/*';
        }

        if ($this->acceptVideo()) {
            $types[] = 'video/*';
        }

        if ($this->acceptAudio()) {
            $types[] = 'audio/*';
        }

        if ($this->acceptDocument()) {
            $types[] = 'application/pdf';
        }

        return $types;
    }

    public function getCurrentFolder(): ?MediaLibraryFolder
    {
        return $this->folderId ? MediaLibraryFolder::find($this->folderId) : null;
    }

    /**
     * @return Collection<int, MediaLibraryFolder>
     */
    public function getBreadcrumbFolders(): Collection
    {
        $breadcrumbs = new Collection;
        $folder = $this->getCurrentFolder();

        while ($folder) {
            $breadcrumbs->prepend($folder);
            $folder = $folder->parent;
        }

        return $breadcrumbs;
    }

    /**
     * @return Collection<int, MediaLibraryFolder>
     */
    public function getFolders(): Collection
    {
        return MediaLibraryFolder::query()
            ->where('parent_id', $this->folderId)
            ->when(filled($this->search), fn ($query) => $query->where('name', 'like', "%{$this->search}%"))
            ->withCount(['children', 'items'])
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<int, MediaLibraryItem>
     */
    public function getItems(): Collection
    {
        $query = MediaLibraryItem::query()
            ->where('folder_id', $this->folderId)
            ->when(filled($this->search), fn ($query) => $query->where(function ($query) {
                $query->where('caption', 'like', "%{$this->search}%")
                    ->orWhereHas('media', fn ($media) => $media->where('file_name', 'like', "%{$this->search}%"));
            }))
            ->with(['media', 'tags']);

        $items = match ($this->sort) {
            'oldest' => $query->oldest()->get(),
            default => $query->latest()->get(),
        };

        return match ($this->sort) {
            'name_asc' => $items->sortBy(fn (MediaLibraryItem $item) => mb_strtolower($item->caption ?: $item->fileName))->values(),
            'name_desc' => $items->sortByDesc(fn (MediaLibraryItem $item) => mb_strtolower($item->caption ?: $item->fileName))->values(),
            default => $items,
        };
    }

    public function getSelectedItem(): ?MediaLibraryItem
    {
        return $this->selectedItemId
            ? MediaLibraryItem::with(['media', 'tags', 'user', 'folder'])->find($this->selectedItemId)
            : null;
    }

    public function getPdfPageCount(MediaLibraryItem $item): ?int
    {
        $media = $item->getFirstMedia('library');

        if (! $media || $media->mime_type !== 'application/pdf') {
            return null;
        }

        try {
            return (new PdfToImage($media->getPath()))->getNumberOfPages();
        } catch (Throwable) {
            return null;
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->uploadAction(),
            $this->newFolderAction(),
        ];
    }

    public function uploadAction(): Action
    {
        return Action::make('upload')
            ->label(ucfirst(__('upload')))
            ->icon('heroicon-o-arrow-up-tray')
            ->schema([
                FileUpload::make('files')
                    ->label(ucfirst(__('file')))
                    ->multiple()
                    ->acceptedFileTypes($this->getAcceptedFileTypes() ?: null)
                    ->disk('public')
                    ->directory('media-library-uploads')
                    ->required(),
            ])
            ->action(function (array $data): void {
                foreach ((array) $data['files'] as $path) {
                    $item = MediaLibraryItem::create([
                        'uploaded_by_user_id' => auth()->id(),
                        'folder_id' => $this->folderId,
                    ]);
                    $item->addMediaFromDisk($path, 'public')->toMediaCollection('library');
                }
            });
    }

    public function newFolderAction(): Action
    {
        return Action::make('newFolder')
            ->label(ucfirst(__('new folder')))
            ->icon('heroicon-o-folder-plus')
            ->schema([
                TextInput::make('name')
                    ->label(ucfirst(__('name')))
                    ->required(),
            ])
            ->action(function (array $data): void {
                MediaLibraryFolder::create([
                    'name' => $data['name'],
                    'parent_id' => $this->folderId,
                ]);
            });
    }

    public function editFolderAction(): Action
    {
        return Action::make('editFolder')
            ->label(ucfirst(__('rename')))
            ->icon('heroicon-o-pencil-square')
            ->schema([
                TextInput::make('name')
                    ->label(ucfirst(__('name')))
                    ->required(),
            ])
            ->fillForm(fn (array $arguments): array => [
                'name' => MediaLibraryFolder::find((int) $arguments['folder'])?->name,
            ])
            ->action(function (array $data, array $arguments): void {
                MediaLibraryFolder::find((int) $arguments['folder'])?->update([
                    'name' => $data['name'],
                ]);
            });
    }

    public function moveFolderAction(): Action
    {
        return Action::make('moveFolder')
            ->label(ucfirst(__('move folder')))
            ->icon('heroicon-o-arrows-right-left')
            ->schema(fn (array $arguments): array => [
                Select::make('parent_id')
                    ->label(ucfirst(__('parent folder')))
                    ->searchable()
                    ->placeholder(ucfirst(__('root folder')))
                    ->options(fn (): array => $this->getFolderOptionsExcludingDescendants((int) $arguments['folder'])),
            ])
            ->fillForm(fn (array $arguments): array => [
                'parent_id' => MediaLibraryFolder::find((int) $arguments['folder'])?->parent_id,
            ])
            ->action(fn (array $data, array $arguments) => MediaLibraryFolder::find((int) $arguments['folder'])
                ?->update(['parent_id' => $data['parent_id']]));
    }

    /**
     * @return array<int, string>
     */
    protected function getFolderOptionsExcludingDescendants(int $folderId): array
    {
        $excludedIds = collect([$folderId]);
        $queue = collect([$folderId]);

        while ($queue->isNotEmpty()) {
            $childIds = MediaLibraryFolder::query()->where('parent_id', $queue->pop())->pluck('id');
            $excludedIds = $excludedIds->merge($childIds);
            $queue = $queue->merge($childIds);
        }

        return MediaLibraryFolder::query()
            ->whereNotIn('id', $excludedIds)
            ->pluck('name', 'id')
            ->all();
    }

    public function deleteFolderAction(): Action
    {
        return Action::make('deleteFolder')
            ->label(ucfirst(__('delete')))
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->modalHeading(ucfirst(__('are you sure you want to delete this folder?')))
            ->modalDescription(ucfirst(__('any files in the folder will not be deleted, but moved to the current folder.')))
            ->modalSubmitActionLabel(ucfirst(__('confirm')))
            ->schema([
                Checkbox::make('delete_content')
                    ->label(ucfirst(__('delete all content in folder'))),
            ])
            ->action(function (array $data, array $arguments): void {
                $folder = MediaLibraryFolder::find((int) $arguments['folder']);

                if (! $folder) {
                    return;
                }

                if ($data['delete_content'] ?? false) {
                    $this->deleteFolderRecursively($folder);
                } else {
                    MediaLibraryFolder::where('parent_id', $folder->id)->update(['parent_id' => $folder->parent_id]);
                    MediaLibraryItem::where('folder_id', $folder->id)->update(['folder_id' => $folder->parent_id]);
                    $folder->delete();
                }

                if ($this->selectedItemId && ! MediaLibraryItem::whereKey($this->selectedItemId)->exists()) {
                    $this->selectedItemId = null;
                }
            });
    }

    protected function deleteFolderRecursively(MediaLibraryFolder $folder): void
    {
        foreach ($folder->children()->get() as $child) {
            $this->deleteFolderRecursively($child);
        }

        MediaLibraryItem::where('folder_id', $folder->id)->get()->each->delete();

        $folder->delete();
    }

    public function editItemAction(): Action
    {
        return Action::make('editItem')
            ->label(ucfirst(__('edit information')))
            ->icon('heroicon-o-pencil-square')
            ->record(fn (array $arguments): ?MediaLibraryItem => MediaLibraryItem::find((int) $arguments['item']))
            ->schema([
                TextInput::make('caption')
                    ->label(ucfirst(__('caption'))),
                TextInput::make('alt_text')
                    ->label(ucfirst(__('alt text'))),
                SpatieTagsInput::make('tags'),
            ])
            ->fillForm(function (array $arguments): array {
                $item = MediaLibraryItem::with('tags')->find((int) $arguments['item']);

                return [
                    'caption' => $item?->caption,
                    'alt_text' => $item?->alt_text,
                    'tags' => $item?->tags->pluck('name')->all() ?? [],
                ];
            })
            ->action(function (array $data, array $arguments): void {
                MediaLibraryItem::find((int) $arguments['item'])?->update([
                    'caption' => $data['caption'],
                    'alt_text' => $data['alt_text'],
                ]);
            });
    }

    public function moveItemAction(): Action
    {
        return Action::make('moveItem')
            ->label(ucfirst(__('move file')))
            ->icon('heroicon-o-folder-arrow-down')
            ->schema([
                Select::make('folder_id')
                    ->label(ucfirst(__('folder')))
                    ->searchable()
                    ->placeholder(ucfirst(__('root folder')))
                    ->options(fn (): array => MediaLibraryFolder::query()->pluck('name', 'id')->all()),
            ])
            ->fillForm(fn (array $arguments): array => [
                'folder_id' => MediaLibraryItem::find((int) $arguments['item'])?->folder_id,
            ])
            ->action(fn (array $data, array $arguments) => MediaLibraryItem::find((int) $arguments['item'])
                ?->update(['folder_id' => $data['folder_id']]));
    }

    public function regenerateItemAction(): Action
    {
        return Action::make('regenerateItem')
            ->label(ucfirst(__('regenerate')))
            ->icon('heroicon-o-arrow-path')
            ->action(function (array $arguments): void {
                $media = MediaLibraryItem::find((int) $arguments['item'])?->getFirstMedia('library');

                if (! $media) {
                    return;
                }

                app(FileManipulator::class)->createDerivedFiles($media);

                Notification::make()
                    ->success()
                    ->title(ucfirst(__('conversions regenerated')))
                    ->send();
            });
    }

    public function deleteItemAction(): Action
    {
        return Action::make('deleteItem')
            ->label(ucfirst(__('delete')))
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->action(function (array $arguments): void {
                MediaLibraryItem::find((int) $arguments['item'])?->delete();

                if ($this->selectedItemId === (int) $arguments['item']) {
                    $this->selectedItemId = null;
                }
            });
    }
}
