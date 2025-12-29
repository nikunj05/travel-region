<?php

namespace App\Filament\Resources\FeaturedHotels;

use App\Filament\Resources\FeaturedHotels\Pages\CreateFeaturedHotel;
use App\Filament\Resources\FeaturedHotels\Pages\EditFeaturedHotel;
use App\Filament\Resources\FeaturedHotels\Pages\ListFeaturedHotels;
use App\Filament\Resources\FeaturedHotels\Schemas\FeaturedHotelForm;
use App\Filament\Resources\FeaturedHotels\Tables\FeaturedHotelsTable;
use App\Models\FeaturedHotel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class FeaturedHotelResource extends Resource
{
    protected static ?string $model = FeaturedHotel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Featured Hotels';
    protected static ?string $label = 'Featured Hotel';
    protected static ?string $navigationLabel = 'Featured Hotels';
    protected static ?string $pluralLabel = 'Featured Hotels';
    protected static ?string $slug = 'featured-hotels';

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
        return FeaturedHotelForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FeaturedHotelsTable::configure($table);
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
            'index' => ListFeaturedHotels::route('/'),
            'create' => CreateFeaturedHotel::route('/create'),
            'edit' => EditFeaturedHotel::route('/{record}/edit'),
        ];
    }
}
