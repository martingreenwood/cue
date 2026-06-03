<?php

namespace App\Filament\Fabricator\PageBlocks;

use App\Filament\Fabricator\Support\PageBlockFields;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class TextColumns extends PageBlock
{
    protected static string $name = 'text-columns';

    public static function defineBlock(Block $block): Block
    {
        return $block
            ->schema([
                TextInput::make('title')
                    ->maxLength(140),
                Textarea::make('subtitle')
                    ->rows(3)
                    ->maxLength(500),
                Section::make('Text columns')
                    ->schema([
                        RichEditor::make('left_content')
                            ->label('Left column')
                            ->required(),
                        RichEditor::make('right_content')
                            ->label('Right column')
                            ->required(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                ...PageBlockFields::buttons(),
                Section::make('Options')
                    ->schema([
                        Select::make('alignment')
                            ->label('Align content')
                            ->options([
                                'left' => 'Left',
                                'center' => 'Center',
                                'right' => 'Right',
                            ])
                            ->default('left')
                            ->required(),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function mutateData(array $data): array
    {
        $data['alignment'] = PageBlockFields::normalizeAlignment(data_get($data, 'alignment'));
        $data['buttons'] = PageBlockFields::normalizeButtons(data_get($data, 'buttons', []));

        return $data;
    }
}
