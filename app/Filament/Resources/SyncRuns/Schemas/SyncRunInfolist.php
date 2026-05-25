<?php

declare(strict_types=1);

namespace App\Filament\Resources\SyncRuns\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SyncRunInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Synchronisation Run')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('id')->label('Run ID'),
                            TextEntry::make('operation')->badge(),
                            TextEntry::make('status')->badge(),
                            TextEntry::make('queued_at')->dateTime(),
                            TextEntry::make('started_at')->dateTime()->placeholder('Not started'),
                            TextEntry::make('finished_at')->dateTime()->placeholder('Not finished'),
                        ]),
                    ])
                    ->columnSpanFull(),
                Section::make('Counts')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('events_synced')->numeric(),
                            TextEntry::make('performances_synced')->numeric(),
                            TextEntry::make('prices_synced')->numeric(),
                            TextEntry::make('performances_queued')->numeric(),
                            TextEntry::make('performances_failed')->numeric(),
                        ]),
                    ])
                    ->columnSpanFull(),
                Section::make('Failure And Context')
                    ->schema([
                        TextEntry::make('error_message')
                            ->placeholder('No error recorded')
                            ->columnSpanFull(),
                        TextEntry::make('context')
                            ->formatStateUsing(fn (?array $state): string => $state === null ? 'No context recorded' : json_encode($state, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR))
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
