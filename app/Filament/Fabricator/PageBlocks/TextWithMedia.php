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

class TextWithMedia extends PageBlock
{
    protected static string $name = 'text-with-media';

    public static function defineBlock(Block $block): Block
    {
        return $block
            ->schema([
                Section::make('Text')
                    ->schema([
                        TextInput::make('title')
                            ->maxLength(140),
                        Textarea::make('subtitle')
                            ->rows(3)
                            ->maxLength(500),
                        RichEditor::make('content')
                            ->label('Text')
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                ...PageBlockFields::mediaAsset(),
                ...PageBlockFields::buttons(),
                Section::make('Options')
                    ->schema([
                        Select::make('layout')
                            ->options([
                                'media_left' => 'Media left, text right',
                                'text_left' => 'Text left, media right',
                            ])
                            ->default('media_left')
                            ->required(),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function mutateData(array $data): array
    {
        $data = PageBlockFields::normalizeMediaData($data);
        $data['layout'] = in_array(data_get($data, 'layout'), ['media_left', 'text_left'], true)
            ? data_get($data, 'layout')
            : 'media_left';
        $data['buttons'] = PageBlockFields::normalizeButtons(data_get($data, 'buttons', []));

        return $data;
    }
}
