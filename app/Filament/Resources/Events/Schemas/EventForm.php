<?php

declare(strict_types=1);

namespace App\Filament\Resources\Events\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Synced Ticketing Source')
                    ->description('Provider-managed values are shown for reference and overwritten by catalogue sync.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('title')
                                ->label('Source title')
                                ->disabled(),
                            TextInput::make('external_id')
                                ->label('Provider event ID')
                                ->disabled(),
                            Textarea::make('summary')
                                ->label('Source summary')
                                ->disabled()
                                ->rows(3),
                            Textarea::make('description_html')
                                ->label('Source description HTML')
                                ->disabled()
                                ->rows(3),
                            TextInput::make('image_url')
                                ->label('Remote image URL')
                                ->disabled()
                                ->columnSpanFull(),
                            Toggle::make('is_on_sale')
                                ->label('Provider on sale')
                                ->disabled(),
                            DateTimePicker::make('synced_at')
                                ->label('Last catalogue sync')
                                ->disabled(),
                        ]),
                    ])
                    ->columnSpanFull(),
                Section::make('Editorial Presentation')
                    ->description('Cue-owned values override provider copy when populated and survive future synchronisation.')
                    ->relationship('editorial')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('title')
                                ->label('Published title override')
                                ->maxLength(255),
                            TextInput::make('slug')
                                ->label('Public slug override')
                                ->maxLength(255)
                                ->unique(ignoreRecord: true),
                            Textarea::make('summary')
                                ->label('Published summary override')
                                ->rows(4)
                                ->columnSpanFull(),
                            RichEditor::make('description_html')
                                ->label('Published description override')
                                ->columnSpanFull(),
                        ]),
                    ])
                    ->columnSpanFull(),
                Section::make('Publication And SEO')
                    ->relationship('editorial')
                    ->schema([
                        Grid::make(2)->schema([
                            Toggle::make('is_published')
                                ->label('Published'),
                            DateTimePicker::make('published_at')
                                ->label('Publish from'),
                            TextInput::make('seo_title')
                                ->label('SEO title')
                                ->maxLength(255)
                                ->columnSpanFull(),
                            Textarea::make('seo_description')
                                ->label('SEO description')
                                ->rows(3)
                                ->columnSpanFull(),
                        ]),
                    ])
                    ->columnSpanFull(),
                Section::make('Managed Hero Image')
                    ->relationship('editorial')
                    ->schema([
                        FileUpload::make('hero_image_path')
                            ->label('Hero image override')
                            ->disk('public')
                            ->directory('events/heroes')
                            ->image()
                            ->imageEditor()
                            ->visibility('public')
                            ->columnSpanFull(),
                        TextInput::make('hero_image_alt')
                            ->label('Hero image alternative text')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
