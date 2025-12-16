<?php

namespace App\Filament\Resources\Cms\Schemas;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CmsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make()
                    ->columns(12)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(6)
                            ->live(onBlur: true) // generates slug when user leaves the field
                            ->afterStateUpdated(fn ($state, callable $set) =>
                                $set('slug', Str::slug($state))
                            ),

                        TextInput::make('slug')
                            ->label('Slug')
                            ->unique(ignoreRecord: true)
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(6),
                    ]),

                Grid::make()
                    ->columns(12)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('sub_title')
                            ->label('Sub Title')
                            ->maxLength(255)
                            ->columnSpan(6),

                            // next rows, full-width
                        FileUpload::make('background_image')
                            ->image()
                            ->imageEditor()
                            ->directory('cms/background-images') // stored inside storage/app/public/cms/background-images
                            ->maxSize(2048) // 2 MB
                            ->disk('public')
                            ->visibility('public')
                            ->downloadable()
                            ->previewable(true)
                            ->openable()
                            ->columnSpan(6),
                    ]),

                Grid::make()
                    ->columns(12)
                    ->columnSpanFull()
                    ->schema([
                        RichEditor::make('content')
                            ->required()
                            ->translatable()
                            ->columnSpan(12),
                    ]),

                Grid::make()
                    ->columns(12)
                    ->columnSpanFull()
                    ->schema([
                        Checkbox::make('about_us')
                            ->label('Is About Us Page?')
                            ->reactive()
                            ->columnSpan(12),
                    ]),

                Grid::make()
                    ->columns(12)
                    ->columnSpanFull()
                    ->visible(fn ($get) => $get('about_us'))
                    ->schema([
                        // next rows, full-width
                        FileUpload::make('founder_image')
                            ->image()
                            ->imageEditor()
                            ->directory('blogs') // stored inside storage/app/public/blogs
                            ->maxSize(2048) // 2 MB
                            ->disk('public')
                            ->visibility('public')
                            ->downloadable()
                            ->previewable(true)
                            ->openable()
                            ->columnSpan(6),
                    ]),

                Repeater::make('why_we_exist')
                    ->label('Why We Exist')
                    ->visible(fn ($get) => $get('about_us'))
                    ->schema([
                        TextInput::make('title')
                            ->label('Title')
                            ->maxLength(255)
                            ->translatable()
                            ->columnSpan(6),

                        TextInput::make('description')
                            ->label('Description')
                            ->maxLength(2048)
                            ->translatable()
                            ->columnSpan(6),

                        FileUpload::make('icon')
                            ->label('Icon')
                            ->image()
                            ->imageEditor()
                            ->directory('cms/why-we-exist-icons')
                            ->maxSize(2048)
                            ->disk('public')
                            ->visibility('public')
                            ->downloadable()
                            ->previewable(true)
                            ->openable()
                            ->columnSpan(12),
                    ])
                    ->collapsible()
                    ->addActionLabel('Add More'),

                Grid::make()
                    ->columns(12)
                    ->columnSpanFull()
                    ->visible(fn ($get) => $get('about_us'))
                    ->schema([
                        FileUpload::make('our_partners')
                            ->label('Our Partners')
                            ->image()
                            ->multiple()
                            ->imageEditor()
                            ->directory('cms/our-partners')
                            ->maxSize(2048)
                            ->disk('public')
                            ->visibility('public')
                            ->downloadable()
                            ->previewable(true)
                            ->openable()
                            ->columnSpan(12),
                    ]),

                Repeater::make('few_highlights')
                    ->label('A Few Highlights')
                    ->visible(fn ($get) => $get('about_us'))
                    ->schema([
                        TextInput::make('title')
                            ->label('Title')
                            ->maxLength(255)
                            ->translatable()
                            ->columnSpan(6),

                        TextInput::make('description')
                            ->label('Description')
                            ->maxLength(2048)
                            ->translatable()
                            ->columnSpan(6),

                        FileUpload::make('icon')
                            ->label('Icon')
                            ->image()
                            ->imageEditor()
                            ->directory('cms/few-highlights-icons')
                            ->maxSize(2048)
                            ->disk('public')
                            ->visibility('public')
                            ->downloadable()
                            ->previewable(true)
                            ->openable()
                            ->columnSpan(12),
                    ])
                    ->collapsible()
                    ->addActionLabel('Add More'),

                Grid::make()
                    ->columns(12)
                    ->columnSpanFull()
                    ->visible(fn ($get) => $get('about_us'))
                    ->schema([
                        TextInput::make('ready_to_explore_title')
                            ->maxLength(255)
                            ->translatable()
                            ->columnSpan(6),

                        TextInput::make('ready_to_explore_sub_title')
                            ->maxLength(255)
                            ->translatable()
                            ->columnSpan(6),

                        FileUpload::make('ready_to_explore_image')
                            ->label('Ready To Explore Background Image')
                            ->image()
                            ->imageEditor()
                            ->directory('cms/ready-to-explore')
                            ->maxSize(2048)
                            ->disk('public')
                            ->visibility('public')
                            ->downloadable()
                            ->previewable(true)
                            ->openable()
                            ->columnSpan(12),
                    ]),
            ]);
    }
}
