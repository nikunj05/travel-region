<?php

namespace App\Filament\Resources\PopularDestinations;

use App\Filament\Resources\PopularDestinations\Pages\CreatePopularDestination;
use App\Filament\Resources\PopularDestinations\Pages\EditPopularDestination;
use App\Filament\Resources\PopularDestinations\Pages\ListPopularDestinations;
use App\Filament\Resources\PopularDestinations\Schemas\PopularDestinationForm;
use App\Filament\Resources\PopularDestinations\Tables\PopularDestinationsTable;
use App\Models\PopularDestination;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class PopularDestinationResource extends Resource
{
    protected static ?string $model = PopularDestination::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::MapPin;

    protected static ?string $recordTitleAttribute = 'Popular Destination';
    protected static ?string $label = 'Popular Destination';
    protected static ?string $navigationLabel = 'Popular Destinations';
    protected static ?string $pluralLabel = 'Popular Destinations';
    protected static ?string $slug = 'popular-destinations';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string | Htmlable
    {
        return $record->name;
    }

    public static function form(Schema $schema): Schema
    {
        return PopularDestinationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PopularDestinationsTable::configure($table);
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
            'index' => ListPopularDestinations::route('/'),
            'create' => CreatePopularDestination::route('/create'),
            'edit' => EditPopularDestination::route('/{record}/edit'),
        ];
    }
}
