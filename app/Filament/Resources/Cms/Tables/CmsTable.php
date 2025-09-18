<?php

namespace App\Filament\Resources\Cms\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CmsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')->searchable()
                    ->formatStateUsing(function ($state) {
                        return match ($state) {
                            'about' => 'About Us',
                            'privacy' => 'Privacy Policy',
                            'terms' => 'Terms & Conditions',
                            default => $state,
                        };
                    }),
                TextColumn::make('title')->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
