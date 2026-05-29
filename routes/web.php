<?php

use App\Http\Controllers\PublicCustomerJourneyController;
use App\Http\Controllers\PublicEventController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

$eventPathPrefix = trim((string) config('ticketing.event_path_prefix', '/events'), '/');

Route::controller(PublicCustomerJourneyController::class)
    ->name('ticketing.')
    ->group(function (): void {
        Route::get('/login', 'login')->name('login');
        Route::get('/login/magic-link', 'magicLink')->name('magic-link');
        Route::get('/account', 'account')->name('account');
        Route::get('/account/profile', 'accountProfile')->name('account.profile');
        Route::get('/account/addresses', 'accountAddresses')->name('account.addresses');
        Route::get('/account/orders', 'accountOrders')->name('account.orders');
        Route::get('/account/payments', 'accountPayments')->name('account.payments');
        Route::get('/account/security', 'accountSecurity')->name('account.security');
        Route::get('/account/contact-preferences', 'accountContactPreferences')->name('account.contact-preferences');
        Route::get('/account/register', 'register')->name('register');
        Route::get('/account/password-reset', 'passwordReset')->name('password-reset');
        Route::get('/basket', 'basket')->name('basket');
        Route::get('/checkout', 'checkout')->name('checkout');
        Route::get('/redeem', 'redeem')->name('redeem');
        Route::get('/renew', 'renew')->name('renew');
        Route::get('/blank', 'blank')->name('blank');
    });

Route::controller(PublicEventController::class)
    ->prefix($eventPathPrefix)
    ->name('events.')
    ->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::get('/{slug}', 'show')->name('show');
    });
