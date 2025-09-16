<?php

namespace App\Filament\Resources\Blogs\Schemas;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
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
                        ->columnSpan(6),

                    TextInput::make('read_time')
                        ->required()
                        ->numeric()
                        ->label('Read Time (in minutes)')
                        ->columnSpan(6),

                    Checkbox::make('is_featured')
                        ->label('Is Featured')
                        ->columnSpan(12), // vertical center

                    // next rows, full-width
                    FileUpload::make('image')
                        ->required()
                        ->directory('blogs') // stored inside storage/app/public/blogs
                        ->maxSize(2048) // 2 MB
                        ->disk('public')
                        ->visibility('public')
                        ->downloadable()
                        ->previewable(true)
                        ->openable()
                        ->columnSpan(12),

                    RichEditor::make('content')
                        ->required()
                        ->columnSpan(12),
                ]),
        ]);
    }
}
