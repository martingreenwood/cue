<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class FileDownloads extends PageBlock
{
    /**
     * @var array<int, string>
     */
    private const ACCEPTED_FILE_TYPES = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];

    protected static string $name = 'file-downloads';

    public static function defineBlock(Block $block): Block
    {
        return $block
            ->schema([
                TextInput::make('title')
                    ->default('Downloads')
                    ->maxLength(140),
                Textarea::make('subtitle')
                    ->rows(3)
                    ->maxLength(500),
                Repeater::make('downloads')
                    ->label('Files')
                    ->schema([
                        TextInput::make('title')
                            ->label('File title')
                            ->required()
                            ->maxLength(160),
                        Textarea::make('description')
                            ->rows(2)
                            ->maxLength(300)
                            ->columnSpanFull(),
                        Select::make('file_source')
                            ->label('Source')
                            ->options([
                                'upload' => 'Upload file',
                                'url' => 'File URL',
                            ])
                            ->default('upload')
                            ->live()
                            ->required(),
                        FileUpload::make('file_upload_path')
                            ->label('Upload file')
                            ->disk('public')
                            ->directory('page-blocks/downloads')
                            ->acceptedFileTypes(self::ACCEPTED_FILE_TYPES)
                            ->visibility('public')
                            ->visible(fn (Get $get): bool => $get('file_source') === 'upload'),
                        TextInput::make('file_url')
                            ->label('File URL')
                            ->url()
                            ->visible(fn (Get $get): bool => $get('file_source') === 'url'),
                        TextInput::make('button_text')
                            ->label('Button text')
                            ->default('View / download')
                            ->maxLength(80),
                    ])
                    ->columns(2)
                    ->defaultItems(1)
                    ->addActionLabel('Add file')
                    ->columnSpanFull(),
            ]);
    }

    public static function mutateData(array $data): array
    {
        $data['downloads'] = self::normalizeDownloads(data_get($data, 'downloads', []));

        return $data;
    }

    /**
     * @param  array<int, array<string, mixed>>|mixed  $downloads
     * @return array<int, array{title: string, description: ?string, url: string, button_text: string, extension: ?string}>
     */
    private static function normalizeDownloads(mixed $downloads): array
    {
        if (! is_array($downloads)) {
            return [];
        }

        return collect($downloads)
            ->map(function (array $download): ?array {
                $title = trim((string) data_get($download, 'title'));
                $url = self::resolveFileUrl($download);

                if ($title === '' || blank($url)) {
                    return null;
                }

                return [
                    'title' => $title,
                    'description' => filled(data_get($download, 'description')) ? (string) data_get($download, 'description') : null,
                    'url' => $url,
                    'button_text' => filled(data_get($download, 'button_text')) ? (string) data_get($download, 'button_text') : 'View / download',
                    'extension' => self::fileExtension($url),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private static function resolveFileUrl(array $download): ?string
    {
        $path = match (data_get($download, 'file_source')) {
            'upload' => self::firstPath(data_get($download, 'file_upload_path')),
            'url' => filled(data_get($download, 'file_url')) ? (string) data_get($download, 'file_url') : null,
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

    private static function firstPath(mixed $path): ?string
    {
        if (is_array($path)) {
            $path = reset($path);
        }

        return filled($path) ? (string) $path : null;
    }

    private static function fileExtension(string $url): ?string
    {
        $extension = pathinfo(parse_url($url, PHP_URL_PATH) ?: '', PATHINFO_EXTENSION);

        return $extension !== '' ? Str::upper($extension) : null;
    }
}
