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

class SupportSettings extends Page
{
    protected string $view = 'filament.pages.support-settings';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::Cog;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';
    protected static ?string $navigationLabel = 'Contact & Support';

    protected static ?int $navigationSort = 4;

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
                            TextInput::make('contact_us')
                                ->columnSpan(6)
                                ->mask('9999 99999 99999')
                                ->label('Contact Us Number'),
                        ]),
                    Grid::make(12)
                        ->schema([
                            TextInput::make('whatsapp_number')
                                ->columnSpan(6)
                                ->mask('9999 99999 99999')
                                ->label('WhatsApp Number'),
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
