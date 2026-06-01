<?php

declare(strict_types=1);

namespace App\Filament\Resources\DonationFunds\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DonationFundForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Donation Fund')
                    ->description('Imported from Spektrix. Configure visibility and ordering for public landing pages.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')
                                ->disabled(),
                            TextInput::make('external_id')
                                ->label('Provider ID')
                                ->disabled(),
                            TextInput::make('code')
                                ->disabled(),
                            TextInput::make('default_donation_amount_minor')
                                ->label('Default amount (minor units)')
                                ->numeric()
                                ->disabled(),
                            Toggle::make('is_visible')
                                ->label('Visible on public pages')
                                ->default(true),
                            TextInput::make('sort_order')
                                ->label('Display order')
                                ->numeric()
                                ->required()
                                ->minValue(0),
                        ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
