<?php

declare(strict_types=1);

namespace App\Filament\Resources\DonationFunds;

use App\Domains\CMS\Models\DonationFund;
use App\Filament\Resources\DonationFunds\Pages\EditDonationFund;
use App\Filament\Resources\DonationFunds\Pages\ListDonationFunds;
use App\Filament\Resources\DonationFunds\Pages\ViewDonationFund;
use App\Filament\Resources\DonationFunds\Schemas\DonationFundForm;
use App\Filament\Resources\DonationFunds\Schemas\DonationFundInfolist;
use App\Filament\Resources\DonationFunds\Tables\DonationFundsTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class DonationFundResource extends Resource
{
    protected static ?string $model = DonationFund::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHeart;

    protected static string|UnitEnum|null $navigationGroup = 'Ticketing';

    protected static ?string $navigationLabel = 'Donation Funds';

    protected static ?string $modelLabel = 'donation fund';

    protected static ?string $pluralModelLabel = 'donation funds';

    protected static ?int $navigationSort = 40;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return DonationFundForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DonationFundInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DonationFundsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDonationFunds::route('/'),
            'view' => ViewDonationFund::route('/{record}'),
            'edit' => EditDonationFund::route('/{record}/edit'),
        ];
    }
}
