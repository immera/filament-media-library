<?php

namespace Immera\FilamentMediaLibrary\Livewire;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Immera\FilamentMediaLibrary\Models\MediaLibraryFolder;
use Immera\FilamentMediaLibrary\Models\MediaLibraryItem;
use Livewire\Component;

class MediaPickerBrowser extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    public ?int $folderId = null;
    public string $search = '';
    public bool $multiple = false;

    /** @var array<string> */
    public array $acceptedFileTypes = [];

    /** @var array<int> */
    public array $selected = [];

    public string $statePath = '';

    /**
     * @param  array<string>  $acceptedFileTypes
     * @param  array<int>  $initialSelection
     */
    public function mount(bool $multiple = false, array $acceptedFileTypes = [], array $initialSelection = [], string $statePath = ''): void
    {
        $this->multiple = $multiple;
        $this->acceptedFileTypes = $acceptedFileTypes;
        $this->selected = $initialSelection;
        $this->statePath = $statePath;
    }

    public function openFolder(?int $folderId): void
    {
        $this->folderId = $folderId;
    }

    public function toggleSelect(int $itemId): void
    {
        if ($this->multiple) {
            $this->selected = in_array($itemId, $this->selected, true)
                ? array_values(array_diff($this->selected, [$itemId]))
                : [...$this->selected, $itemId];

            return;
        }

        $this->selected = in_array($itemId, $this->selected, true) ? [] : [$itemId];
    }

    public function confirm(): void
    {
        $value = $this->multiple ? $this->selected : ($this->selected[0] ?? null);

        $this->dispatch('media-picker-confirmed', statePath: $this->statePath, value: $value);
    }

    public function close(): void
    {
        $this->dispatch('media-picker-closed', statePath: $this->statePath);
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
        return MediaLibraryItem::query()
            ->where('folder_id', $this->folderId)
            ->when(filled($this->search), fn ($query) => $query->where(function ($query) {
                $query->where('caption', 'like', "%{$this->search}%")
                    ->orWhereHas('media', fn ($media) => $media->where('file_name', 'like', "%{$this->search}%"));
            }))
            ->when(filled($this->acceptedFileTypes), fn ($query) => $query->whereHas(
                'media',
                fn ($media) => $media->where(function ($media) {
                    foreach ($this->acceptedFileTypes as $type) {
                        $media->orWhere('mime_type', 'like', str_replace('*', '%', $type));
                    }
                })
            ))
            ->with(['media', 'tags'])
            ->latest()
            ->get();
    }

    public function uploadAction(): Action
    {
        return Action::make('upload')
            ->label(__('Upload'))
            ->icon('heroicon-o-arrow-up-tray')
            ->schema([
                FileUpload::make('files')
                    ->label(__('file'))
                    ->multiple()
                    ->when(
                        filled($this->acceptedFileTypes),
                        fn (FileUpload $fileUpload) => $fileUpload->acceptedFileTypes($this->acceptedFileTypes),
                    )
                    ->disk('public')
                    ->directory('media-library-uploads')
                    ->required(),
            ])
            ->action(function (array $data): void {
                $newIds = [];

                foreach ((array) $data['files'] as $path) {
                    $item = MediaLibraryItem::create([
                        'uploaded_by_user_id' => auth()->id(),
                        'folder_id' => $this->folderId,
                    ]);
                    $item->addMediaFromDisk($path, 'public')->toMediaCollection('library');
                    $newIds[] = $item->getKey();
                }

                if ($this->multiple) {
                    $this->selected = [...$this->selected, ...$newIds];

                    return;
                }

                $this->selected = [$newIds[0] ?? null];
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

    public function render(): View
    {
        return view('filament-media-library::livewire.media-picker-browser');
    }
}
