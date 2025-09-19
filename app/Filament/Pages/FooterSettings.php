<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class FooterSettings extends Page
{
    protected string $view = 'filament.pages.footer-settings';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::Cog;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';
    protected static ?string $navigationLabel = 'Footer Settings';

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
                            TextInput::make('copyright')
                                ->maxLength(255)
                                ->columnSpan(6),

                            FileUpload::make('footer_logo')
                                ->image()
                                ->imageEditor()
                                ->directory('settings')
                                ->maxSize(2048)
                                ->disk('public')
                                ->visibility('public')
                                ->downloadable()
                                ->previewable(true)
                                ->openable()
                                ->columnSpan(12),

                            Textarea::make('footer_info')
                                ->columnSpan(12),

                            // CheckboxList::make('footer_explore_items')
                            //     ->options([
                            //         'destination' => 'Destination',
                            //         'deals' => 'Deals',
                            //         'blog' => 'Blog',
                            //         'limited_offers' => 'Limited Offers',
                            //     ])->columnSpan(4),

                            // CheckboxList::make('footer_about_items')
                            //     ->options([
                            //         'about_us' => 'About Us',
                            //         'our_story' => 'Our Story',
                            //         'our_promise' => 'Our Promise',
                            //     ])->columnSpan(4),

                            // CheckboxList::make('footer_support_items')
                            //     ->options([
                            //         'help_center' => 'Help Center',
                            //         'faqs' => 'FAQs',
                            //         'contact_us' => 'Contact Us',
                            //         'booking_policy' => 'Booking Policy',
                            //     ])->columnSpan(4),
                        ]),

                    Repeater::make('social_media_links')
                        ->label('Social Media Links')
                        ->schema([
                            TextInput::make('title')
                                ->label('Title')
                                ->placeholder('e.g. Facebook')
                                ->maxLength(255)
                                ->columnSpan(6),

                            TextInput::make('link')
                                ->label('Link')
                                ->placeholder('e.g. https://www.facebook.com/yourpage')
                                ->url()
                                ->maxLength(2048)
                                ->columnSpan(6)
                                ->helperText('Please include the full URL, including http:// or https://'),

                            FileUpload::make('icon')
                                ->label('Icon')
                                ->image()
                                ->imageEditor()
                                ->directory('settings/social-media-icons')
                                ->maxSize(2048)
                                ->disk('public')
                                ->visibility('public')
                                ->downloadable()
                                ->previewable(true)
                                ->openable()
                                ->columnSpan(6),
                        ])
                        ->columns(12)
                        ->collapsible()
                        ->addActionLabel('Add Social Link'),
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
