<?php

declare(strict_types=1);

namespace App\Filament\Resources\DonationFunds\Pages;

use App\Filament\Resources\DonationFunds\DonationFundResource;
use Filament\Resources\Pages\ListRecords;

class ListDonationFunds extends ListRecords
{
    protected static string $resource = DonationFundResource::class;
}
