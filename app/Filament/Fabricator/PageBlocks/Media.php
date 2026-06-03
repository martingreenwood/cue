<?php

namespace App\Filament\Fabricator\PageBlocks;

use App\Filament\Fabricator\Support\PageBlockFields;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Textarea;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class Media extends PageBlock
{
    protected static string $name = 'media';

    public static function defineBlock(Block $block): Block
    {
        return $block
            ->schema([
                ...PageBlockFields::mediaAsset(),
                Textarea::make('caption')
                    ->rows(2)
                    ->maxLength(300)
                    ->columnSpanFull(),
            ]);
    }

    public static function mutateData(array $data): array
    {
        return PageBlockFields::normalizeMediaData($data);
    }
}
