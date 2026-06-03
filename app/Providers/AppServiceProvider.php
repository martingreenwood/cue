<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domains\CMS\Services\PublicSiteCopyService;
use App\Domains\Ticketing\Contracts\TicketingProvider;
use App\Infrastructure\Ticketing\Spektrix\SpektrixTicketingProvider;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Z3d0X\FilamentFabricator\Facades\FilamentFabricator;
use Z3d0X\FilamentFabricator\View\ResourceSchemaSlot;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(TicketingProvider::class, SpektrixTicketingProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        FilamentFabricator::registerSchemaSlot(ResourceSchemaSlot::SIDEBAR_AFTER, [
            Section::make('Featured image')
                ->schema([
                    FileUpload::make('featured_image_path')
                        ->label('Featured image')
                        ->disk('public')
                        ->directory('pages/featured-images')
                        ->image()
                        ->imageEditor()
                        ->visibility('public')
                        ->columnSpanFull(),
                    TextInput::make('featured_image_alt')
                        ->label('Featured image alternative text')
                        ->maxLength(255)
                        ->columnSpanFull(),
                ])
                ->columnSpanFull(),
        ]);

        View::composer('layouts.public', function ($view): void {
            $data = $view->getData();

            if (! array_key_exists('siteCopy', $data)) {
                $view->with('siteCopy', app(PublicSiteCopyService::class)->current());
            }

            if (! array_key_exists('customerSession', $data)) {
                $view->with('customerSession', app(TicketingProvider::class)->customerSession());
            }
        });
    }
}
