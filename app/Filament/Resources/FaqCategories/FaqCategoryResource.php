<?php

namespace App\Filament\Resources\FaqCategories;

use App\Filament\Resources\FaqCategories\Pages\CreateFaqCategory;
use App\Filament\Resources\FaqCategories\Pages\EditFaqCategory;
use App\Filament\Resources\FaqCategories\Pages\ListFaqCategories;
use App\Filament\Resources\FaqCategories\Schemas\FaqCategoryForm;
use App\Filament\Resources\FaqCategories\Tables\FaqCategoriesTable;
use App\Models\FaqCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class FaqCategoryResource extends Resource
{
    protected static ?string $model = FaqCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'FAQs';
    protected static ?string $recordTitleAttribute = 'Category';
    protected static ?string $label = 'FAQ Category';
    protected static ?string $navigationLabel = 'FAQ Categories';
    protected static ?string $pluralLabel = 'FAQ Categories';
    protected static ?string $slug = 'faq-categories';

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
        return FaqCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FaqCategoriesTable::configure($table);
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
            'index' => ListFaqCategories::route('/'),
            'create' => CreateFaqCategory::route('/create'),
            'edit' => EditFaqCategory::route('/{record}/edit'),
        ];
    }
}
