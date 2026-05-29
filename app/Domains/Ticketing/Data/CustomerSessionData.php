<?php

declare(strict_types=1);

namespace App\Domains\Ticketing\Data;

final readonly class CustomerSessionData
{
    public function __construct(
        public string $clientName,
        public ?string $customDomain,
        public string $componentLoaderUrl,
        public string $customerUrl,
        public string $updateCustomerUrl,
        public string $statementsUrl,
        public string $agreedStatementsUrl,
        public string $addressesUrl,
        public string $countriesUrl,
        public string $postcodeLookupUrl,
        public string $ordersUrl,
        public string $printAtHomeDocumentsUrl,
        public string $storedCardsUrl,
        public string $changePasswordUrl,
        public string $forgotPasswordUrl,
        public string $deauthenticateUrl,
        public string $basketUrl,
        public string $basketTicketsUrl,
        public string $basketMerchandiseUrl,
        public string $stockItemsUrl,
        public string $initiateDirectPaymentUrl,
        public string $initiateCustomerPaymentUrl,
    ) {}
}
