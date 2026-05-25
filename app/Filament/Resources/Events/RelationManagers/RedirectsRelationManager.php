<?php

declare(strict_types=1);

namespace App\Filament\Resources\Events\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RedirectsRelationManager extends RelationManager
{
    protected static string $relationship = 'redirects';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('source_path')
                    ->label('Incoming path')
                    ->required()
                    ->startsWith('/')
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('destination_path')
                    ->label('Destination path')
                    ->required()
                    ->startsWith('/')
                    ->maxLength(255),
                Select::make('status_code')
                    ->label('Redirect type')
                    ->options([
                        301 => '301 Permanent',
                        302 => '302 Temporary',
                    ])
                    ->default(301)
                    ->required(),
                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
                Textarea::make('notes')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('source_path')
            ->columns([
                TextColumn::make('source_path')->label('Incoming path')->searchable(),
                TextColumn::make('destination_path')->label('Destination path')->searchable(),
                TextColumn::make('status_code')->label('Status')->badge(),
                IconColumn::make('is_active')->label('Active')->boolean(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
