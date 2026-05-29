<?php

declare(strict_types=1);

namespace App\Filament\Resources\FilterTerms\Tables;

use App\Domains\Events\Enums\FilterGroup;
use App\Domains\Events\Models\FilterTerm;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FilterTermsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Public label')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('filter_group')
                    ->label('Group')
                    ->formatStateUsing(fn (FilterGroup $state): string => $state->label())
                    ->badge()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label('Identifier')
                    ->searchable(),
                TextColumn::make('description')
                    ->label('Guidance')
                    ->limit(60)
                    ->toggleable(),
                TextColumn::make('assignments')
                    ->label('Assigned')
                    ->state(fn (FilterTerm $record): int => match ($record->filter_group) {
                        FilterGroup::What => (int) $record->what_events_count,
                        FilterGroup::Offers => (int) $record->offer_events_count,
                        FilterGroup::Access => (int) $record->access_performances_count,
                    })
                    ->numeric()
                    ->sortable(false),
                TextColumn::make('sort_order')
                    ->label('Order')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('filter_group')
                    ->label('Group')
                    ->options(FilterGroup::options()),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->modifyQueryUsing(fn (Builder $query): Builder => $query
                ->withCount([
                    'accessPerformances',
                    'offerEvents',
                    'whatEvents',
                ])
                ->orderByRaw(
                    'case filter_group when ? then 0 when ? then 1 when ? then 2 else 3 end',
                    [
                        FilterGroup::What->value,
                        FilterGroup::Offers->value,
                        FilterGroup::Access->value,
                    ],
                )
                ->orderBy('sort_order')
                ->orderBy('name'));
    }
}
