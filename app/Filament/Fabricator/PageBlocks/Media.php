<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class Media extends PageBlock
{
    protected static string $name = 'media';

    public static function defineBlock(Block $block): Block
    {
        return $block
            ->schema([
                TextInput::make('media_url')
                    ->label('Media URL')
                    ->url()
                    ->required(),
                TextInput::make('media_alt')
                    ->label('Media alt text')
                    ->maxLength(255),
                Textarea::make('caption')
                    ->rows(2)
                    ->maxLength(300),
            ]);
    }

    public static function mutateData(array $data): array
    {
        return $data;
    }
}
