<?php

declare(strict_types=1);

namespace App\Infrastructure\Ticketing\Spektrix;

use App\Domains\Ticketing\Data\CustomerSessionData;

final class SpektrixCustomDomainReadiness
{
    private const string ComponentLoaderUrl = 'https://webcomponents.spektrix.com/stable/spektrix-component-loader.js';

    public function configuredCustomDomainBaseUrl(): ?string
    {
        $baseUrl = config('ticketing.providers.spektrix.customer_facing_base_url');

        return is_string($baseUrl) && $baseUrl !== '' ? rtrim($baseUrl, '/') : null;
    }

    public function activeBookingBaseUrl(): ?string
    {
        $customDomainBaseUrl = $this->configuredCustomDomainBaseUrl();

        if (
            $customDomainBaseUrl !== null
            && $this->isConfirmedBySpektrix()
            && $this->isActivatableCustomDomainBaseUrl($customDomainBaseUrl)
        ) {
            return $customDomainBaseUrl;
        }

        $fallbackBaseUrl = config('ticketing.providers.spektrix.iframe_base_url');

        if (! is_string($fallbackBaseUrl) || $fallbackBaseUrl === '') {
            return null;
        }

        $fallbackBaseUrl = rtrim($fallbackBaseUrl, '/');
        $hostname = parse_url($fallbackBaseUrl, PHP_URL_HOST);

        if ($hostname === 'system.spektrix.com') {
            return $fallbackBaseUrl;
        }

        return $this->isConfirmedBySpektrix() && $this->isActivatableCustomDomainBaseUrl($fallbackBaseUrl)
            ? $fallbackBaseUrl
            : null;
    }

    public function inspectedBaseUrl(): ?string
    {
        return $this->configuredCustomDomainBaseUrl() ?? $this->activeBookingBaseUrl();
    }

    public function hostname(): ?string
    {
        $baseUrl = $this->inspectedBaseUrl();

        if ($baseUrl === null) {
            return null;
        }

        $hostname = parse_url($baseUrl, PHP_URL_HOST);

        return is_string($hostname) && $hostname !== '' ? $hostname : null;
    }

    public function usesCustomDomain(): bool
    {
        $hostname = $this->hostname();

        return $hostname !== null && $hostname !== 'system.spektrix.com';
    }

    public function hasClientPath(): bool
    {
        $baseUrl = $this->inspectedBaseUrl();

        if ($baseUrl === null) {
            return false;
        }

        $path = parse_url($baseUrl, PHP_URL_PATH);

        return is_string($path) && trim($path, '/') !== '';
    }

    public function usesHttps(): bool
    {
        $baseUrl = $this->inspectedBaseUrl();

        return $baseUrl !== null && parse_url($baseUrl, PHP_URL_SCHEME) === 'https';
    }

    public function isConfirmedBySpektrix(): bool
    {
        return (bool) config('ticketing.providers.spektrix.custom_domain_confirmed', false);
    }

    public function customerSession(): ?CustomerSessionData
    {
        $baseUrl = $this->activeBookingBaseUrl();

        if ($baseUrl === null) {
            return null;
        }

        $path = parse_url($baseUrl, PHP_URL_PATH);
        $hostname = parse_url($baseUrl, PHP_URL_HOST);

        if (! is_string($path) || ! is_string($hostname) || $hostname === '') {
            return null;
        }

        $clientName = explode('/', trim($path, '/'))[0];

        if ($clientName === '') {
            return null;
        }

        return new CustomerSessionData(
            clientName: $clientName,
            customDomain: $hostname === 'system.spektrix.com' ? null : $hostname,
            componentLoaderUrl: self::ComponentLoaderUrl,
            customerUrl: rtrim($baseUrl, '/').'/api/v3/customer',
            updateCustomerUrl: rtrim($baseUrl, '/').'/api/v3/customer',
            statementsUrl: rtrim($baseUrl, '/').'/api/v3/statements',
            agreedStatementsUrl: rtrim($baseUrl, '/').'/api/v3/customer/agreed-statements',
            addressesUrl: rtrim($baseUrl, '/').'/api/v3/customer/addresses',
            countriesUrl: rtrim($baseUrl, '/').'/api/v3/countries',
            postcodeLookupUrl: rtrim($baseUrl, '/').'/api/v3/postcode-lookup',
            ordersUrl: rtrim($baseUrl, '/').'/api/v3/customer/orders',
            printAtHomeDocumentsUrl: rtrim($baseUrl, '/').'/api/v3/print-at-home-documents',
            storedCardsUrl: rtrim($baseUrl, '/').'/api/v3/customer/stored-cards',
            changePasswordUrl: rtrim($baseUrl, '/').'/api/v3/customer/change-password',
            forgotPasswordUrl: rtrim($baseUrl, '/').'/api/v3/customer/forgot-password',
            deauthenticateUrl: rtrim($baseUrl, '/').'/api/v3/customer/deauthenticate',
        );
    }

    private function isActivatableCustomDomainBaseUrl(string $baseUrl): bool
    {
        $hostname = parse_url($baseUrl, PHP_URL_HOST);
        $path = parse_url($baseUrl, PHP_URL_PATH);

        return parse_url($baseUrl, PHP_URL_SCHEME) === 'https'
            && is_string($hostname)
            && $hostname !== ''
            && $hostname !== 'system.spektrix.com'
            && is_string($path)
            && trim($path, '/') !== '';
    }
}
