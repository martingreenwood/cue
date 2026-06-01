<?php

declare(strict_types=1);

namespace App\Domains\CMS\Models;

use Carbon\CarbonImmutable;
use Database\Factories\Domains\CMS\Models\DonationFundFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property CarbonImmutable|null $synced_at
 */
class DonationFund extends Model
{
    /** @use HasFactory<DonationFundFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'provider',
        'external_id',
        'name',
        'description',
        'code',
        'default_donation_amount_minor',
        'is_visible',
        'sort_order',
        'source_payload',
        'synced_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'default_donation_amount_minor' => 'integer',
            'is_visible' => 'boolean',
            'sort_order' => 'integer',
            'source_payload' => 'array',
            'synced_at' => 'immutable_datetime',
        ];
    }

    protected static function newFactory(): DonationFundFactory
    {
        return DonationFundFactory::new();
    }
}
