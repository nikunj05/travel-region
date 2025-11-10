<?php

namespace App\Filament\Pages;

use App\Forms\Components\GradientColorPicker;
use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class HeroContent extends Page
{
    protected string $view = 'filament.pages.hero-content';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Film;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';
    protected static ?string $navigationLabel = 'Hero Content';

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
                    Section::make('Home Page Hero Content')
                        ->schema([
                            TextInput::make('home_title')
                                ->columnSpan(12)
                                ->translatable()
                                ->label('Hero Title'),

                            TextInput::make('home_subtitle')
                                ->columnSpan(12)
                                ->translatable()
                                ->label('Hero Subtitle'),

                            FileUpload::make('home_hero_image')
                                ->label('Hero Image English')
                                ->image()
                                ->imageEditor()
                                ->directory('hero-content')
                                ->maxSize(5048)
                                ->disk('public')
                                ->visibility('public')
                                ->downloadable()
                                ->previewable(true)
                                ->openable()
                                ->columnSpan(12),

                            FileUpload::make('home_hero_image_ar')
                                ->label('Hero Image Arabic')
                                ->image()
                                ->imageEditor()
                                ->directory('hero-content')
                                ->maxSize(5048)
                                ->disk('public')
                                ->visibility('public')
                                ->downloadable()
                                ->previewable(true)
                                ->openable()
                                ->columnSpan(12),
                        ]),

                    Section::make('FAQ Page')
                        ->schema([
                            // ColorPicker::make('faq_background_color')
                            //     ->columnSpan(12)
                            //     ->label('Background Color'),

                            GradientColorPicker::make('faq_background_color')
                                ->label('Background Color')
                                ->maxColors(5)
                                ->columnSpan(12)
                                ->direction('to right')
                                ->columnSpanFull(),
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
