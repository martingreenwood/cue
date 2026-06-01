<?php

declare(strict_types=1);

namespace App\Domains\CMS\Models;

use Carbon\CarbonImmutable;
use Database\Factories\Domains\CMS\Models\MembershipFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property CarbonImmutable|null $synced_at
 */
class Membership extends Model
{
    /** @use HasFactory<MembershipFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'provider',
        'external_id',
        'name',
        'description',
        'html_description',
        'image_url',
        'thumbnail_url',
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
            'is_visible' => 'boolean',
            'sort_order' => 'integer',
            'source_payload' => 'array',
            'synced_at' => 'immutable_datetime',
        ];
    }

    protected static function newFactory(): MembershipFactory
    {
        return MembershipFactory::new();
    }
}
