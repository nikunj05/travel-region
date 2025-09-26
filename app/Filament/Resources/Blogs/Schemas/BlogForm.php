<?php

namespace App\Filament\Resources\Blogs\Schemas;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class BlogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make()
                ->columns(12)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('title')
                        ->required()
                        ->maxLength(255)
                        ->translatable()
                        ->columnSpan(6),

                    TagsInput::make('tags')
                        ->columnSpan(6),

                    Select::make('category_id')
                        ->label('Category')
                        ->relationship('category', 'name') // uses relation
                        ->searchable()
                        ->preload()
                        ->required()
                        ->columnSpan(6),

                    TextInput::make('read_time')
                        ->required()
                        ->numeric()
                        ->label('Read Time (in minutes)')
                        ->columnSpan(6),

                    // next rows, full-width
                    FileUpload::make('image')
                        ->required()
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

                    Checkbox::make('is_featured')
                        ->label('Is Featured')
                        ->columnSpan(6), // vertical center

                    RichEditor::make('content')
                        ->required()
                        ->translatable()
                        ->columnSpan(12),
                ]),

            Grid::make()
                ->columns(12)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('author')
                        ->maxLength(255)
                        ->columnSpan(6),

                    TextInput::make('author_info')
                        ->maxLength(255)
                        ->columnSpan(6),

                    // next rows, full-width
                    FileUpload::make('author_image')
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
        ]);
    }
}
