<?php

namespace App\Filament\Resources\CommissionByCities;

use App\Filament\Resources\CommissionByCities\Pages\CreateCommissionByCity;
use App\Filament\Resources\CommissionByCities\Pages\EditCommissionByCity;
use App\Filament\Resources\CommissionByCities\Pages\ListCommissionByCities;
use App\Filament\Resources\CommissionByCities\Schemas\CommissionByCityForm;
use App\Filament\Resources\CommissionByCities\Tables\CommissionByCitiesTable;
use App\Models\CommissionByCity;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class CommissionByCityResource extends Resource
{
    protected static ?string $model = CommissionByCity::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::BuildingOffice2;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $recordTitleAttribute = 'Commission By City';
    protected static ?string $label = 'Commission By City';
    protected static ?string $navigationLabel = 'Commission By Cities';
    protected static ?string $pluralLabel = 'Commission By Cities';
    protected static ?string $slug = 'commission-by-cities';

    protected static ?int $navigationSort = 7;

    public static function getGloballySearchableAttributes(): array
    {
        return ['city'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string | Htmlable
    {
        return $record->city;
    }

    public static function form(Schema $schema): Schema
    {
        return CommissionByCityForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CommissionByCitiesTable::configure($table);
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
            'index' => ListCommissionByCities::route('/'),
            'create' => CreateCommissionByCity::route('/create'),
            'edit' => EditCommissionByCity::route('/{record}/edit'),
        ];
    }
}
