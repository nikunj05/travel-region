<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class Settings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected string $view = 'filament.pages.settings';

    protected static ?int $navigationSort = 5;

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill($this->getRecord()?->attributesToArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    Grid::make(12)
                        ->schema([
                            FileUpload::make('logo')
                                ->required()
                                ->directory('settings')
                                ->maxSize(2048)
                                ->disk('public')
                                ->visibility('public')
                                ->downloadable()
                                ->previewable(true)
                                ->openable()
                                ->columnSpan(6),

                            FileUpload::make('favicon')
                                ->required()
                                ->directory('settings')
                                ->maxSize(2048)
                                ->disk('public')
                                ->visibility('public')
                                ->downloadable()
                                ->previewable(true)
                                ->openable()
                                ->columnSpan(6),
                        ]),
                ])
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([
                        Action::make('save')
                            ->submit('save')
                            ->keyBindings(['mod+s']),
                    ]),
                ]),
            ])
            ->record($this->getRecord())
            ->statePath('data');
    }

    public function getRecord(): ?Setting
    {
        return Setting::first();
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $record = $this->getRecord();

        if (! $record) {
            $record = new Setting();
        }

        $record->fill($data);
        $record->save();

        if ($record->wasRecentlyCreated) {
            $this->form->record($record)->saveRelationships();
        }

        Notification::make()
            ->success()
            ->title('Saved')
            ->send();
    }
}
