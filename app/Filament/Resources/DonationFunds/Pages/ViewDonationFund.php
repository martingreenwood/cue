<?php

declare(strict_types=1);

namespace App\Filament\Resources\DonationFunds\Pages;

use App\Filament\Resources\DonationFunds\DonationFundResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewDonationFund extends ViewRecord
{
    protected static string $resource = DonationFundResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
