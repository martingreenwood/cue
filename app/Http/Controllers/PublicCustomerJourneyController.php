<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\CMS\Services\PublicSiteCopyService;
use App\Domains\Ticketing\Contracts\TicketingProvider;
use App\Domains\Ticketing\Enums\CustomerJourney;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class PublicCustomerJourneyController extends Controller
{
    public function __construct(
        private readonly PublicSiteCopyService $publicSiteCopy,
        private readonly TicketingProvider $ticketingProvider,
    ) {}

    public function login(): View
    {
        return $this->renderAuthenticationView('ticketing.login');
    }

    public function register(): View
    {
        return $this->renderAuthenticationView('ticketing.register');
    }

    public function passwordReset(): View
    {
        return $this->renderJourney(CustomerJourney::PasswordReset);
    }

    public function redeem(): View
    {
        return $this->renderJourney(CustomerJourney::Redeem);
    }

    public function renew(): View
    {
        return $this->renderJourney(CustomerJourney::Renew);
    }

    public function magicLink(): View
    {
        return $this->renderAuthenticationView('ticketing.magic-link');
    }

    public function account(): View
    {
        return $this->renderAccountView('profile');
    }

    public function accountProfile(): View
    {
        return $this->renderAccountView('profile');
    }

    public function accountAddresses(): View
    {
        return $this->renderAccountView('addresses');
    }

    public function accountOrders(): View
    {
        return $this->renderAccountView('orders');
    }

    public function accountPayments(): View
    {
        return $this->renderAccountView('payments');
    }

    public function accountSecurity(): View
    {
        return $this->renderAccountView('security');
    }

    public function accountContactPreferences(): View
    {
        return $this->renderAccountView('contact-preferences');
    }

    public function basket(): View
    {
        $customerSession = $this->ticketingProvider->customerSession();

        if ($customerSession === null) {
            throw new NotFoundHttpException;
        }

        return view('ticketing.basket', [
            'siteCopy' => $this->publicSiteCopy->current(),
            'customerSession' => $customerSession,
        ]);
    }

    public function checkout(): View
    {
        $customerSession = $this->ticketingProvider->customerSession();

        if ($customerSession === null) {
            throw new NotFoundHttpException;
        }

        return view('ticketing.checkout', [
            'siteCopy' => $this->publicSiteCopy->current(),
            'customerSession' => $customerSession,
        ]);
    }

    public function checkoutConfirmation(): View
    {
        $customerSession = $this->ticketingProvider->customerSession();

        if ($customerSession === null) {
            throw new NotFoundHttpException;
        }

        return view('ticketing.checkout-confirmation', [
            'siteCopy' => $this->publicSiteCopy->current(),
            'customerSession' => $customerSession,
        ]);
    }

    public function blank(): View
    {
        return view('ticketing.blank');
    }

    private function renderAccountView(string $section): View
    {
        $customerSession = $this->ticketingProvider->customerSession();

        if ($customerSession === null) {
            throw new NotFoundHttpException;
        }

        return view('ticketing.account', [
            'siteCopy' => $this->publicSiteCopy->current(),
            'customerSession' => $customerSession,
            'activeSection' => $section,
        ]);
    }

    private function renderJourney(CustomerJourney $journey): View
    {
        $surface = $this->ticketingProvider->customerJourney($journey);

        if ($surface === null) {
            throw new NotFoundHttpException;
        }

        return view('ticketing.customer-journey', [
            'journey' => $journey,
            'surface' => $surface,
            'siteCopy' => $this->publicSiteCopy->current(),
            'customerSession' => $this->ticketingProvider->customerSession(),
        ]);
    }

    private function renderAuthenticationView(string $view): View
    {
        $authentication = $this->ticketingProvider->customerAuthentication();

        if ($authentication === null) {
            throw new NotFoundHttpException;
        }

        return view($view, [
            'authentication' => $authentication,
            'siteCopy' => $this->publicSiteCopy->current(),
            'customerSession' => $this->ticketingProvider->customerSession(),
        ]);
    }
}
