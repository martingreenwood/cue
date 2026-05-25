<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * @return array<string, mixed>
 */
function spektrixEventPayload(array $overrides = []): array
{
    return array_replace([
        'id' => '70401ASVSQQQCRPSCNRQPCKDQQQTGLMGK',
        'name' => 'Aldwych Theatre -> Integration Controls 01',
        'description' => 'Description',
        'htmlDescription' => '<div>Description</div>',
        'duration' => 120,
        'imageUrl' => 'https://system.spektrix.com/apitesting/files/event.jpg',
        'thumbnailUrl' => 'https://system.spektrix.com/apitesting/files/event-thumbnail.jpg',
        'altText' => 'Production image',
        'isOnSale' => true,
        'firstInstanceDateTimeUtc' => '2026-06-28T19:00:00Z',
        'lastInstanceDateTimeUtc' => '2026-06-29T19:00:00Z',
    ], $overrides);
}

/**
 * @return array<string, mixed>
 */
function spektrixPerformancePayload(array $overrides = []): array
{
    return array_replace_recursive([
        'id' => '112659AKSSQTLRRKDQBVSTLTJPSGVLTTN',
        'event' => ['id' => '70401ASVSQQQCRPSCNRQPCKDQQQTGLMGK'],
        'planId' => '201AGBHDRLQHNHPHKKMPKLGPMDRDTDMVL',
        'priceList' => ['id' => '7801ATTBPVLSHPLVGKMJNMMDGBTHMPBTK'],
        'startUtc' => '2026-06-28T19:00:00Z',
        'startSellingAtWebUtc' => '2026-01-16T16:30:00Z',
        'stopSellingAtWebUtc' => '2026-06-28T18:00:00Z',
        'webInstanceId' => null,
        'isOnSale' => true,
        'cancelled' => false,
    ], $overrides);
}

/**
 * @return array<string, mixed>
 */
function spektrixPriceListPayload(): array
{
    return [
        'id' => '7801ATTBPVLSHPLVGKMJNMMDGBTHMPBTK',
        'prices' => [
            [
                'id' => 'default-band-a',
                'isBandDefault' => true,
                'amount' => 40.00,
                'ticketType' => [
                    'id' => 'full-price',
                    'name' => 'Full Price',
                    'attribute_EligibleForDynamicPricing' => true,
                ],
                'priceBand' => ['id' => 'band-a', 'name' => 'Band A'],
            ],
            [
                'id' => 'default-band-c',
                'isBandDefault' => true,
                'amount' => 20.00,
                'ticketType' => [
                    'id' => 'full-price',
                    'name' => 'Full Price',
                    'attribute_EligibleForDynamicPricing' => true,
                ],
                'priceBand' => ['id' => 'band-c', 'name' => 'Band C'],
            ],
            [
                'id' => 'student-band-c',
                'isBandDefault' => false,
                'amount' => 15.00,
                'ticketType' => [
                    'id' => 'student',
                    'name' => 'Student',
                    'attribute_EligibleForDynamicPricing' => false,
                ],
                'priceBand' => ['id' => 'band-c', 'name' => 'Band C'],
            ],
        ],
    ];
}
