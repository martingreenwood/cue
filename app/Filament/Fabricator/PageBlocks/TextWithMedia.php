<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class TextWithMedia extends PageBlock
{
    protected static string $name = 'text-with-media';

    public static function defineBlock(Block $block): Block
    {
        return $block
            ->schema([
                RichEditor::make('content')
                    ->required(),
                TextInput::make('media_url')
                    ->label('Media URL')
                    ->url()
                    ->required(),
                TextInput::make('media_alt')
                    ->label('Media alt text')
                    ->maxLength(255),
            ])
            ->columns(2);
    }

    public static function mutateData(array $data): array
    {
        return $data;
    }
}
