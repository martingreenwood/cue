<?php

declare(strict_types=1);

namespace App\Filament\Resources\Memberships\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MembershipInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Membership')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('name'),
                            TextEntry::make('external_id')->label('Provider ID'),
                            TextEntry::make('image_url')->placeholder('Not set'),
                            TextEntry::make('thumbnail_url')->placeholder('Not set'),
                            IconEntry::make('is_visible')
                                ->label('Visible')
                                ->boolean(),
                            TextEntry::make('sort_order')
                                ->numeric(),
                            TextEntry::make('description')
                                ->placeholder('Not set')
                                ->columnSpanFull(),
                        ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
