<?php

declare(strict_types=1);

namespace App\Filament\Resources\FilterTerms;

use App\Domains\Events\Models\FilterTerm;
use App\Filament\Resources\FilterTerms\Pages\CreateFilterTerm;
use App\Filament\Resources\FilterTerms\Pages\EditFilterTerm;
use App\Filament\Resources\FilterTerms\Pages\ListFilterTerms;
use App\Filament\Resources\FilterTerms\Pages\ViewFilterTerm;
use App\Filament\Resources\FilterTerms\Schemas\FilterTermForm;
use App\Filament\Resources\FilterTerms\Schemas\FilterTermInfolist;
use App\Filament\Resources\FilterTerms\Tables\FilterTermsTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class FilterTermResource extends Resource
{
    protected static ?string $model = FilterTerm::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Filter Terms';

    protected static ?string $modelLabel = 'filter term';

    protected static ?string $pluralModelLabel = 'filter terms';

    protected static ?int $navigationSort = 20;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return FilterTermForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return FilterTermInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FilterTermsTable::configure($table);
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
            'index' => ListFilterTerms::route('/'),
            'create' => CreateFilterTerm::route('/create'),
            'view' => ViewFilterTerm::route('/{record}'),
            'edit' => EditFilterTerm::route('/{record}/edit'),
        ];
    }
}
