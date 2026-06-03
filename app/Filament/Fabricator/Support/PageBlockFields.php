<?php

namespace App\Filament\Fabricator\Support;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Z3d0X\FilamentFabricator\Facades\FilamentFabricator;
use Z3d0X\FilamentFabricator\Models\Page;

class PageBlockFields
{
    /**
     * @return array<int, Component>
     */
    public static function heroContent(): array
    {
        return [
            Section::make('Content')
                ->schema([
                    TextInput::make('title')
                        ->required()
                        ->maxLength(140),
                    Textarea::make('subtitle')
                        ->rows(3)
                        ->maxLength(500),
                    RichEditor::make('text')
                        ->label('Text')
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->columnSpanFull(),
        ];
    }

    /**
     * @return array<int, Component>
     */
    public static function heroMedia(): array
    {
        return [
            Section::make('Image')
                ->schema([
                    Select::make('image_source')
                        ->label('Source')
                        ->options([
                            'none' => 'No image',
                            'library' => 'Select from library',
                            'upload' => 'Upload new image',
                            'url' => 'Image URL',
                        ])
                        ->default('none')
                        ->live()
                        ->required(),
                    Select::make('image_library_path')
                        ->label('Library image')
                        ->options(fn (): array => self::publicAssetOptions('page-blocks/images'))
                        ->searchable()
                        ->visible(fn (Get $get): bool => $get('image_source') === 'library'),
                    FileUpload::make('image_upload_path')
                        ->label('Upload image')
                        ->disk('public')
                        ->directory('page-blocks/images')
                        ->image()
                        ->imageEditor()
                        ->visibility('public')
                        ->visible(fn (Get $get): bool => $get('image_source') === 'upload'),
                    TextInput::make('image_url')
                        ->label('Image URL')
                        ->url()
                        ->visible(fn (Get $get): bool => $get('image_source') === 'url'),
                    TextInput::make('image_alt')
                        ->label('Image alternative text')
                        ->maxLength(255)
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->columnSpanFull(),
            Section::make('Video')
                ->schema([
                    Select::make('video_source')
                        ->label('Source')
                        ->options([
                            'none' => 'No video',
                            'url' => 'Hosted video URL',
                            'upload' => 'Upload video',
                        ])
                        ->default('none')
                        ->live()
                        ->required(),
                    TextInput::make('video_url')
                        ->label('Hosted video URL')
                        ->url()
                        ->visible(fn (Get $get): bool => $get('video_source') === 'url'),
                    FileUpload::make('video_upload_path')
                        ->label('Upload video')
                        ->disk('public')
                        ->directory('page-blocks/videos')
                        ->acceptedFileTypes(['video/mp4', 'video/webm', 'video/ogg'])
                        ->visibility('public')
                        ->visible(fn (Get $get): bool => $get('video_source') === 'upload'),
                    Grid::make(3)
                        ->schema([
                            Toggle::make('video_autoplay')
                                ->label('Autoplay')
                                ->default(true),
                            Toggle::make('video_muted')
                                ->label('Muted')
                                ->default(true),
                            Toggle::make('video_loop')
                                ->label('Loop')
                                ->default(true),
                        ])
                        ->visible(fn (Get $get): bool => $get('video_source') !== 'none')
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->columnSpanFull(),
            Section::make('Options')
                ->schema([
                    Toggle::make('overlay_enabled')
                        ->label('Enable overlay')
                        ->default(true),
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
                ->columns(2)
                ->columnSpanFull(),
        ];
    }

    /**
     * @return array<int, Component>
     */
    public static function mediaAsset(): array
    {
        return [
            Section::make('Media')
                ->schema([
                    Select::make('media_type')
                        ->label('Type')
                        ->options([
                            'image' => 'Image',
                            'video' => 'Video',
                        ])
                        ->default('image')
                        ->live()
                        ->required(),
                    Select::make('image_source')
                        ->label('Image source')
                        ->options([
                            'library' => 'Select from library',
                            'upload' => 'Upload new image',
                            'url' => 'Image URL',
                        ])
                        ->default('library')
                        ->live()
                        ->required()
                        ->visible(fn (Get $get): bool => $get('media_type') === 'image'),
                    Select::make('image_library_path')
                        ->label('Library image')
                        ->options(fn (): array => self::publicAssetOptions('page-blocks/images'))
                        ->searchable()
                        ->visible(fn (Get $get): bool => $get('media_type') === 'image' && $get('image_source') === 'library'),
                    FileUpload::make('image_upload_path')
                        ->label('Upload image')
                        ->disk('public')
                        ->directory('page-blocks/images')
                        ->image()
                        ->imageEditor()
                        ->visibility('public')
                        ->visible(fn (Get $get): bool => $get('media_type') === 'image' && $get('image_source') === 'upload'),
                    TextInput::make('image_url')
                        ->label('Image URL')
                        ->url()
                        ->visible(fn (Get $get): bool => $get('media_type') === 'image' && $get('image_source') === 'url'),
                    Select::make('video_source')
                        ->label('Video source')
                        ->options([
                            'library' => 'Select from library',
                            'upload' => 'Upload new video',
                            'url' => 'Hosted video URL',
                        ])
                        ->default('library')
                        ->live()
                        ->required()
                        ->visible(fn (Get $get): bool => $get('media_type') === 'video'),
                    Select::make('video_library_path')
                        ->label('Library video')
                        ->options(fn (): array => self::publicAssetOptions('page-blocks/videos'))
                        ->searchable()
                        ->visible(fn (Get $get): bool => $get('media_type') === 'video' && $get('video_source') === 'library'),
                    FileUpload::make('video_upload_path')
                        ->label('Upload video')
                        ->disk('public')
                        ->directory('page-blocks/videos')
                        ->acceptedFileTypes(['video/mp4', 'video/webm', 'video/ogg'])
                        ->visibility('public')
                        ->visible(fn (Get $get): bool => $get('media_type') === 'video' && $get('video_source') === 'upload'),
                    TextInput::make('video_url')
                        ->label('Hosted video URL')
                        ->url()
                        ->visible(fn (Get $get): bool => $get('media_type') === 'video' && $get('video_source') === 'url'),
                    TextInput::make('media_alt')
                        ->label('Alternative text')
                        ->maxLength(255)
                        ->visible(fn (Get $get): bool => $get('media_type') === 'image')
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->columnSpanFull(),
        ];
    }

    /**
     * @return array<int, Component>
     */
    public static function buttons(): array
    {
        return [
            Section::make('Buttons')
                ->schema([
                    Repeater::make('buttons')
                        ->label('Buttons')
                        ->schema([
                            TextInput::make('text')
                                ->label('Button text')
                                ->required()
                                ->maxLength(80),
                            Select::make('variant')
                                ->options([
                                    'primary' => 'Primary',
                                    'secondary' => 'Secondary',
                                    'link' => 'Link',
                                ])
                                ->default('primary')
                                ->required(),
                            Select::make('link_type')
                                ->label('Link type')
                                ->options([
                                    'page' => 'Internal page',
                                    'route' => 'Named route',
                                    'url' => 'External URL',
                                ])
                                ->default('page')
                                ->live()
                                ->required(),
                            Select::make('page_id')
                                ->label('Page')
                                ->options(fn (): array => self::pageOptions())
                                ->searchable()
                                ->visible(fn (Get $get): bool => $get('link_type') === 'page'),
                            TextInput::make('route_name')
                                ->label('Route name')
                                ->helperText('Example: login')
                                ->visible(fn (Get $get): bool => $get('link_type') === 'route'),
                            TextInput::make('url')
                                ->label('External URL')
                                ->url()
                                ->visible(fn (Get $get): bool => $get('link_type') === 'url'),
                            Select::make('target')
                                ->options([
                                    '_self' => 'Same tab',
                                    '_blank' => 'New tab',
                                ])
                                ->default('_self')
                                ->required(),
                        ])
                        ->columns(2)
                        ->defaultItems(0)
                        ->addActionLabel('Add button')
                        ->columnSpanFull(),
                ])
                ->columnSpanFull(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function normalizeHeroData(array $data): array
    {
        $data['image_src'] = self::resolveMediaUrl(
            source: data_get($data, 'image_source', 'none'),
            libraryPath: data_get($data, 'image_library_path'),
            uploadPath: data_get($data, 'image_upload_path'),
            url: data_get($data, 'image_url'),
        );

        $data['video_src'] = self::resolveMediaUrl(
            source: data_get($data, 'video_source', 'none'),
            uploadPath: data_get($data, 'video_upload_path'),
            url: data_get($data, 'video_url'),
        );

        $data['buttons'] = self::normalizeButtons(data_get($data, 'buttons', []));
        $data['alignment'] = self::normalizeAlignment(data_get($data, 'alignment'));

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function normalizeMediaData(array $data): array
    {
        $data['media_type'] = in_array(data_get($data, 'media_type'), ['image', 'video'], true)
            ? data_get($data, 'media_type')
            : 'image';

        $data['image_src'] = self::resolveMediaUrl(
            source: data_get($data, 'image_source', data_get($data, 'media_url') ? 'url' : 'library'),
            libraryPath: data_get($data, 'image_library_path'),
            uploadPath: data_get($data, 'image_upload_path'),
            url: data_get($data, 'image_url', data_get($data, 'media_url')),
        );

        $data['video_src'] = self::resolveMediaUrl(
            source: data_get($data, 'video_source', 'library'),
            libraryPath: data_get($data, 'video_library_path'),
            uploadPath: data_get($data, 'video_upload_path'),
            url: data_get($data, 'video_url'),
        );

        return $data;
    }

    public static function normalizeAlignment(mixed $alignment): string
    {
        return in_array($alignment, ['left', 'center', 'right'], true)
            ? $alignment
            : 'left';
    }

    /**
     * @param  array<int, array<string, mixed>>|mixed  $buttons
     * @return array<int, array{text: string, url: string, target: string, variant: string}>
     */
    public static function normalizeButtons(mixed $buttons): array
    {
        if (! is_array($buttons)) {
            return [];
        }

        return collect($buttons)
            ->map(function (array $button): ?array {
                $url = self::resolveButtonUrl($button);
                $text = trim((string) data_get($button, 'text'));

                if ($text === '' || blank($url)) {
                    return null;
                }

                return [
                    'text' => $text,
                    'url' => $url,
                    'target' => in_array(data_get($button, 'target'), ['_self', '_blank'], true)
                        ? data_get($button, 'target')
                        : '_self',
                    'variant' => in_array(data_get($button, 'variant'), ['primary', 'secondary', 'link'], true)
                        ? data_get($button, 'variant')
                        : 'primary',
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private static function publicAssetOptions(string $directory): array
    {
        return collect(Storage::disk('public')->files($directory))
            ->mapWithKeys(fn (string $path): array => [$path => Str::of($path)->afterLast('/')->replace(['-', '_'], ' ')->title()->toString()])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private static function pageOptions(): array
    {
        return Page::query()
            ->orderBy('title')
            ->pluck('title', 'id')
            ->all();
    }

    private static function resolveMediaUrl(mixed $source, mixed $libraryPath = null, mixed $uploadPath = null, mixed $url = null): ?string
    {
        $path = match ($source) {
            'library' => self::firstPath($libraryPath),
            'upload' => self::firstPath($uploadPath),
            'url' => filled($url) ? (string) $url : null,
            default => null,
        };

        if (blank($path)) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://', '/'])) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }

    private static function resolveButtonUrl(array $button): ?string
    {
        return match (data_get($button, 'link_type')) {
            'page' => filled(data_get($button, 'page_id'))
                ? FilamentFabricator::getPageUrlFromId(data_get($button, 'page_id'))
                : null,
            'route' => self::routeUrl(data_get($button, 'route_name')),
            'url' => filled(data_get($button, 'url')) ? (string) data_get($button, 'url') : null,
            default => null,
        };
    }

    private static function routeUrl(mixed $routeName): ?string
    {
        if (blank($routeName) || ! Route::has((string) $routeName)) {
            return null;
        }

        return route((string) $routeName, absolute: false);
    }

    private static function firstPath(mixed $path): ?string
    {
        if (is_array($path)) {
            $path = reset($path);
        }

        return filled($path) ? (string) $path : null;
    }
}
