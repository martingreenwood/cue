<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Storage;
use Z3d0X\FilamentFabricator\Facades\FilamentFabricator;
use Z3d0X\FilamentFabricator\Models\Page;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class RelatedContent extends PageBlock
{
    protected static string $name = 'related-content';

    public static function defineBlock(Block $block): Block
    {
        return $block
            ->schema([
                Select::make('page_id')
                    ->label('Page')
                    ->options(fn (): array => Page::query()
                        ->orderBy('title')
                        ->pluck('title', 'id')
                        ->all())
                    ->searchable()
                    ->required(),
                TextInput::make('eyebrow')
                    ->label('Section title')
                    ->default('Related content')
                    ->maxLength(80),
                TextInput::make('title')
                    ->label('Title override')
                    ->maxLength(140),
                Textarea::make('text')
                    ->label('Text')
                    ->rows(3)
                    ->maxLength(500),
                TextInput::make('button_text')
                    ->label('Button text')
                    ->default('Read more')
                    ->maxLength(80),
            ])
            ->columns(2);
    }

    public static function mutateData(array $data): array
    {
        $page = filled(data_get($data, 'page_id'))
            ? Page::query()->find(data_get($data, 'page_id'))
            : null;

        $data['related_title'] = filled(data_get($data, 'title'))
            ? (string) data_get($data, 'title')
            : $page?->title;
        $data['related_url'] = $page
            ? FilamentFabricator::getPageUrlFromId($page->id)
            : null;
        $data['button_text'] = filled(data_get($data, 'button_text'))
            ? (string) data_get($data, 'button_text')
            : 'Read more';
        $data['eyebrow'] = filled(data_get($data, 'eyebrow'))
            ? (string) data_get($data, 'eyebrow')
            : 'Related content';
        $data['featured_image_src'] = filled($page?->featured_image_path)
            ? Storage::disk('public')->url($page->featured_image_path)
            : null;
        $data['featured_image_alt'] = $page?->featured_image_alt;

        return $data;
    }
}
