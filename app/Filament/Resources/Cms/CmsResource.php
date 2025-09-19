<?php

namespace App\Filament\Resources\Cms;

use App\Filament\Resources\Cms\Pages\CreateCms;
use App\Filament\Resources\Cms\Pages\EditCms;
use App\Filament\Resources\Cms\Pages\ListCms;
use App\Filament\Resources\Cms\Schemas\CmsForm;
use App\Filament\Resources\Cms\Tables\CmsTable;
use App\Models\Cms;
use App\Models\CmsPage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class CmsResource extends Resource
{
    protected static ?string $model = CmsPage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::BookOpen;

    protected static ?string $recordTitleAttribute = 'Page';
    protected static ?string $label = 'Page';
    protected static ?string $navigationLabel = 'CMS';
    protected static ?string $pluralLabel = 'Pages';
    protected static ?string $slug = 'cms';

    protected static ?int $navigationSort = 6;

    public static function getGloballySearchableAttributes(): array
    {
        return ['title'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string | Htmlable
    {
        return $record->title;
    }

    public static function form(Schema $schema): Schema
    {
        return CmsForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CmsTable::configure($table);
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
            'index' => ListCms::route('/'),
            'create' => CreateCms::route('/create'),
            'edit' => EditCms::route('/{record}/edit'),
        ];
    }
}
