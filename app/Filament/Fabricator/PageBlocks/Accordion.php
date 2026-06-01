<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class Accordion extends PageBlock
{
    protected static string $name = 'accordion';

    public static function defineBlock(Block $block): Block
    {
        return $block
            ->schema([
                Repeater::make('items')
                    ->required()
                    ->minItems(1)
                    ->defaultItems(1)
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(160),
                        RichEditor::make('content')
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->columns(1)
                    ->columnSpanFull(),
            ]);
    }

    public static function mutateData(array $data): array
    {
        return $data;
    }
}
