<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\RichEditor;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class TextColumns extends PageBlock
{
    protected static string $name = 'text-columns';

    public static function defineBlock(Block $block): Block
    {
        return $block
            ->schema([
                RichEditor::make('left_content')
                    ->label('Left column')
                    ->required(),
                RichEditor::make('right_content')
                    ->label('Right column')
                    ->required(),
            ])
            ->columns(2);
    }

    public static function mutateData(array $data): array
    {
        return $data;
    }
}
