<?php

declare(strict_types=1);

namespace App\Domains\Ticketing\Data;

final readonly class CustomerAuthenticationData
{
    public function __construct(
        public string $authenticateUrl,
        public string $createCustomerUrl,
        public string $sendMagicLinkUrl,
        public string $authenticateMagicLinkUrl,
    ) {}
}
