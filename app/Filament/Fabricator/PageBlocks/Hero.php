<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class Hero extends PageBlock
{
    protected static string $name = 'hero';

    public static function defineBlock(Block $block): Block
    {
        return $block
            ->schema([
                TextInput::make('title')
                    ->required()
                    ->maxLength(140),
                Textarea::make('subtitle')
                    ->rows(3)
                    ->maxLength(500),
            ]);
    }

    public static function mutateData(array $data): array
    {
        return $data;
    }
}
