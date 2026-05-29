<?php

declare(strict_types=1);

namespace App\Domains\Events\Enums;

enum FilterGroup: string
{
    case What = 'what';
    case Offers = 'offers';
    case Access = 'access';

    public function label(): string
    {
        return match ($this) {
            self::What => 'What',
            self::Offers => 'Offers',
            self::Access => 'Access',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $group): array => [$group->value => $group->label()])
            ->all();
    }
}
