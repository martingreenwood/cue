<?php

namespace App\Filament\Fabricator\PageBlocks;

use App\Filament\Fabricator\Support\PageBlockFields;
use Filament\Forms\Components\Builder\Block;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class Hero extends PageBlock
{
    protected static string $name = 'hero';

    public static function defineBlock(Block $block): Block
    {
        return $block
            ->schema([
                ...PageBlockFields::heroContent(),
                ...PageBlockFields::heroMedia(),
                ...PageBlockFields::buttons(),
            ]);
    }

    public static function mutateData(array $data): array
    {
        return PageBlockFields::normalizeHeroData($data);
    }
}
