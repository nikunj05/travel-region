<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class CommissionSetting extends Page
{
    protected string $view = 'filament.pages.commission-setting';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::PercentBadge;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';
    protected static ?string $navigationLabel = 'Commission Settings';

    protected static ?int $navigationSort = 6;

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
                            TextInput::make('five_star_commission')
                                ->label('5 Star Commission (%)')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(100)
                                ->columnSpan(6),

                            TextInput::make('four_star_commission')
                                ->label('4 Star Commission (%)')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(100)
                                ->columnSpan(6),

                            TextInput::make('three_star_commission')
                                ->label('3 Star Commission (%)')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(100)
                                ->columnSpan(6),

                            TextInput::make('two_star_commission')
                                ->label('2 Star Commission (%)')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(100)
                                ->columnSpan(6),

                            TextInput::make('one_star_commission')
                                ->label('1 Star Commission (%)')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(100)
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
