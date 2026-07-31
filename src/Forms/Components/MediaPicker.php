<?php

namespace Immera\FilamentMediaLibrary\Forms\Components;

use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\ModalTableSelect;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Immera\FilamentMediaLibrary\Models\MediaLibraryItem;

class MediaPicker extends ModalTableSelect
{
    protected string $view = 'filament-media-library::components.media-picker';
    protected bool|Closure $shouldShowFileName = false;
    protected bool|Closure $isDownloadable = false;
    protected string|Closure|null $uploadButtonLabel = null;

    /** @var array<string> | Closure */
    protected array|Closure $acceptedTypes = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->getOptionLabelUsing(function (): ?string {
            $item = Arr::first($this->getSelectedItems());

            return $item ? ($item->caption ?: $item->fileName) : null;
        });

        $this->getOptionLabelsUsing(function (): array {
            return collect($this->getSelectedItems())
                ->map(fn (MediaLibraryItem $item): ?string => $item->caption ?: $item->fileName)
                ->all();
        });

        $this->registerActions([
            fn (self $component): Action => $component->getClearAction(),
        ]);
    }

    public function getInValidationRuleValues(): ?array
    {
        return null;
    }

    public function getSelectAction(): Action
    {
        return Action::make('select')
            ->label(fn (): string => $this->evaluate($this->uploadButtonLabel) ?? __('Choose file'))
            ->modalSubmitAction(false)
            ->modalCancelAction(false)
            ->modalWidth(Width::FourExtraLarge)
            ->modalContent(fn (): View => view('filament-media-library::components.media-picker-modal-content', [
                'multiple' => $this->isMultiple(),
                'acceptedFileTypes' => $this->getAcceptedFileTypes(),
                'initialSelection' => Arr::wrap($this->getState() ?? []),
                'statePath' => $this->getStatePath(),
            ]));
    }

    public function showFileName(bool|Closure $condition = true): static
    {
        $this->shouldShowFileName = $condition;

        return $this;
    }

    public function shouldShowFileName(): bool
    {
        return (bool) $this->evaluate($this->shouldShowFileName);
    }

    public function downloadable(bool|Closure $condition = true): static
    {
        $this->isDownloadable = $condition;

        return $this;
    }

    public function isDownloadable(): bool
    {
        return (bool) $this->evaluate($this->isDownloadable);
    }

    public function buttonLabel(string|Closure $label): static
    {
        $this->uploadButtonLabel = $label;

        return $this;
    }

    /**
     * @param  array<string> | Closure  $types
     */
    public function acceptedFileTypes(array|Closure $types): static
    {
        $this->acceptedTypes = $types;

        return $this;
    }

    /**
     * @return array<string>
     */
    public function getAcceptedFileTypes(): array
    {
        return (array) $this->evaluate($this->acceptedTypes);
    }

    /**
     * @return array<MediaLibraryItem>
     */
    public function getSelectedItems(): array
    {
        if (! isset($this->container)) {
            return [];
        }

        $state = $this->getState();
        $ids = $this->isMultiple() ? (array) ($state ?? []) : (filled($state) ? [$state] : []);

        if (empty($ids)) {
            return [];
        }

        return MediaLibraryItem::query()
            ->with('media')
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id')
            ->only($ids)
            ->all();
    }

    public function getClearAction(): Action
    {
        return Action::make('clear')
            ->label(__('clear'))
            ->icon('heroicon-o-x-mark')
            ->color('danger')
            ->requiresConfirmation()
            ->action(function (): void {
                $this->state($this->isMultiple() ? [] : null);
                $this->callAfterStateUpdated();
            });
    }
}
