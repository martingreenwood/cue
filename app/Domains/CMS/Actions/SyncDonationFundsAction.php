<?php

declare(strict_types=1);

namespace App\Domains\CMS\Actions;

use App\Domains\CMS\Models\DonationFund;
use App\Domains\Ticketing\Contracts\TicketingProvider;
use App\Domains\Ticketing\Data\FundData;

final class SyncDonationFundsAction
{
    public function __construct(private readonly TicketingProvider $provider) {}

    public function execute(): int
    {
        $funds = $this->provider->funds();
        $externalIds = [];

        $funds->each(function (FundData $fund) use (&$externalIds): void {
            $externalIds[] = $fund->externalId;

            DonationFund::updateOrCreate(
                [
                    'provider' => $this->provider->providerKey(),
                    'external_id' => $fund->externalId,
                ],
                [
                    'name' => $fund->name,
                    'description' => $fund->description,
                    'code' => $fund->code,
                    'default_donation_amount_minor' => $fund->defaultDonationAmountMinor,
                    'source_payload' => $fund->sourcePayload,
                    'synced_at' => now(),
                ],
            );
        });

        $staleFunds = DonationFund::query()
            ->where('provider', $this->provider->providerKey());

        if ($externalIds !== []) {
            $staleFunds->whereNotIn('external_id', $externalIds);
        }

        $staleFunds->delete();

        return $funds->count();
    }
}
