<?php

declare(strict_types=1);

namespace App\Domains\CMS\Actions;

use App\Domains\CMS\Models\Membership;
use App\Domains\Ticketing\Contracts\TicketingProvider;
use App\Domains\Ticketing\Data\MembershipData;

final class SyncMembershipsAction
{
    public function __construct(private readonly TicketingProvider $provider) {}

    public function execute(): int
    {
        $memberships = $this->provider->memberships();
        $externalIds = [];

        $memberships->each(function (MembershipData $membership) use (&$externalIds): void {
            $externalIds[] = $membership->externalId;

            Membership::updateOrCreate(
                [
                    'provider' => $this->provider->providerKey(),
                    'external_id' => $membership->externalId,
                ],
                [
                    'name' => $membership->name,
                    'description' => $membership->description,
                    'html_description' => $membership->htmlDescription,
                    'image_url' => $membership->imageUrl,
                    'thumbnail_url' => $membership->thumbnailUrl,
                    'source_payload' => $membership->sourcePayload,
                    'synced_at' => now(),
                ],
            );
        });

        $staleMemberships = Membership::query()
            ->where('provider', $this->provider->providerKey());

        if ($externalIds !== []) {
            $staleMemberships->whereNotIn('external_id', $externalIds);
        }

        $staleMemberships->delete();

        return $memberships->count();
    }
}
