<?php

declare(strict_types=1);

namespace App\Filament\Resources\Events\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EventInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Editorial Presentation')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('editorial.title')
                                ->label('Published title override')
                                ->placeholder('Using provider title'),
                            TextEntry::make('editorial.slug')
                                ->label('Public slug override')
                                ->placeholder('Using provider slug'),
                            IconEntry::make('editorial.is_published')
                                ->label('Published')
                                ->boolean(),
                            TextEntry::make('editorial.published_at')
                                ->label('Publish from')
                                ->dateTime()
                                ->placeholder('Not scheduled'),
                            TextEntry::make('editorial.summary')
                                ->label('Published summary override')
                                ->placeholder('Using provider summary')
                                ->columnSpanFull(),
                            TextEntry::make('editorial.seo_title')
                                ->label('SEO title')
                                ->placeholder('Not set'),
                            TextEntry::make('editorial.seo_description')
                                ->label('SEO description')
                                ->placeholder('Not set'),
                        ]),
                    ])
                    ->columnSpanFull(),
                Section::make('Public Filters')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('whatTerms.name')
                                ->label('What')
                                ->badge()
                                ->placeholder('No terms assigned'),
                            TextEntry::make('offerTerms.name')
                                ->label('Offers')
                                ->badge()
                                ->placeholder('No terms assigned'),
                        ]),
                    ])
                    ->columnSpanFull(),
                Section::make('Synced Ticketing Source')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('title')->label('Source title'),
                            TextEntry::make('external_id')->label('Provider event ID'),
                            TextEntry::make('provider')->label('Provider')->badge(),
                            IconEntry::make('is_on_sale')->label('On sale')->boolean(),
                            TextEntry::make('first_performance_at')->dateTime(),
                            TextEntry::make('last_performance_at')->dateTime(),
                            TextEntry::make('summary')->label('Source summary')->columnSpanFull(),
                            ImageEntry::make('local_image_path')
                                ->label('Downloaded source image')
                                ->disk('public')
                                ->height(200)
                                ->placeholder('Not yet downloaded')
                                ->columnSpanFull(),
                            TextEntry::make('image_url')
                                ->label('Remote image URL')
                                ->url(fn (string $state): string => $state)
                                ->openUrlInNewTab()
                                ->columnSpanFull(),
                            TextEntry::make('synced_at')->label('Last synchronised')->dateTime(),
                        ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
