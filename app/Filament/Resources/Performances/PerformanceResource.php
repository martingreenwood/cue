<?php

declare(strict_types=1);

namespace App\Filament\Resources\Performances;

use App\Domains\Events\Models\Performance;
use App\Filament\Resources\Performances\Pages\EditPerformance;
use App\Filament\Resources\Performances\Pages\ListPerformances;
use App\Filament\Resources\Performances\Pages\ViewPerformance;
use App\Filament\Resources\Performances\RelationManagers\PricesRelationManager;
use App\Filament\Resources\Performances\Schemas\PerformanceForm;
use App\Filament\Resources\Performances\Schemas\PerformanceInfolist;
use App\Filament\Resources\Performances\Tables\PerformancesTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PerformanceResource extends Resource
{
    protected static ?string $model = Performance::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    protected static ?string $navigationLabel = 'Performances';

    protected static ?string $recordTitleAttribute = 'external_id';

    public static function form(Schema $schema): Schema
    {
        return PerformanceForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PerformanceInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PerformancesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            PricesRelationManager::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['event', 'accessTerms']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPerformances::route('/'),
            'view' => ViewPerformance::route('/{record}'),
            'edit' => EditPerformance::route('/{record}/edit'),
        ];
    }
}
