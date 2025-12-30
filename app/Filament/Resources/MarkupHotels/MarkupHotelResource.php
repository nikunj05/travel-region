<?php

namespace App\Filament\Resources\MarkupHotels;

use App\Filament\Resources\MarkupHotels\Pages\CreateMarkupHotel;
use App\Filament\Resources\MarkupHotels\Pages\EditMarkupHotel;
use App\Filament\Resources\MarkupHotels\Pages\ListMarkupHotels;
use App\Filament\Resources\MarkupHotels\Schemas\MarkupHotelForm;
use App\Filament\Resources\MarkupHotels\Tables\MarkupHotelsTable;
use App\Models\MarkupHotel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class MarkupHotelResource extends Resource
{
    protected static ?string $model = MarkupHotel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ReceiptPercent;

    protected static ?string $recordTitleAttribute = 'Markup Hotels';
    protected static ?string $label = 'Markup Hotel';
    protected static ?string $navigationLabel = 'Markup Hotels';
    protected static ?string $pluralLabel = 'Markup Hotels';
    protected static ?string $slug = 'markup-hotels';

    protected static ?int $navigationSort = 1;

    public static function getGloballySearchableAttributes(): array
    {
        return ['hotel_code'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string | Htmlable
    {
        return $record->hotel_code;
    }

    public static function form(Schema $schema): Schema
    {
        return MarkupHotelForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MarkupHotelsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMarkupHotels::route('/'),
            'create' => CreateMarkupHotel::route('/create'),
            'edit' => EditMarkupHotel::route('/{record}/edit'),
        ];
    }
}
