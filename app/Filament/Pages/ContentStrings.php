<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Domains\CMS\Services\PublicSiteCopyService;
use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * @property-read Schema $form
 */
class ContentStrings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleBottomCenterText;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Content Strings';

    protected static ?string $title = 'Content Strings';

    protected static ?string $slug = 'content-strings';

    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.pages.content-strings';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public function mount(PublicSiteCopyService $publicSiteCopy): void
    {
        $this->form->fill($publicSiteCopy->current()->toArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Programme And Pricing')
                    ->description('Wording shown alongside locally synchronised programme and guide-price information.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('listing_kicker')
                                ->label('Event listing kicker')
                                ->required()
                                ->maxLength(255),
                            TextInput::make('guide_price_label')
                                ->label('Guide price heading')
                                ->required()
                                ->maxLength(255),
                            TextInput::make('guide_price_prefix')
                                ->label('Price prefix')
                                ->helperText('Displayed immediately before the formatted price, for example "Guide price from £25.00".')
                                ->required()
                                ->maxLength(255),
                            TextInput::make('prices_confirmed_in_booking')
                                ->label('No displayed price message')
                                ->required()
                                ->maxLength(255),
                            TextInput::make('dynamic_price_suffix')
                                ->label('Dynamic price suffix')
                                ->required()
                                ->maxLength(255),
                            TextInput::make('stale_price_suffix')
                                ->label('Stale price suffix')
                                ->required()
                                ->maxLength(255),
                            Textarea::make('performance_freshness_notice')
                                ->label('Performance list freshness notice')
                                ->helperText('Explain that schedule and guide-price information is locally stored and final booking information is checked securely.')
                                ->required()
                                ->rows(3)
                                ->columnSpanFull(),
                        ]),
                    ])
                    ->columnSpanFull(),
                Section::make('Booking Handoff')
                    ->description('Cue-owned language surrounding the provider booking frame. Content inside the iframe remains provider-managed.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('booking_cta_label')
                                ->label('Booking button label')
                                ->required()
                                ->maxLength(255),
                            TextInput::make('online_booking_unavailable_label')
                                ->label('Unavailable booking label')
                                ->required()
                                ->maxLength(255),
                            Textarea::make('secure_booking_prefix')
                                ->label('Selected performance booking introduction')
                                ->helperText('The selected performance date and time are appended automatically.')
                                ->required()
                                ->rows(2)
                                ->columnSpanFull(),
                            Textarea::make('footer_availability_notice')
                                ->label('Public footer availability notice')
                                ->required()
                                ->rows(2)
                                ->columnSpanFull(),
                        ]),
                    ])
                    ->columnSpanFull(),
                Section::make('Customer Account And Basket')
                    ->description('Wording exposed by the customer utility bar. Login and basket state remains managed securely by the ticketing provider.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('customer_logged_in_label')
                                ->label('Signed in status prefix')
                                ->required()
                                ->maxLength(255),
                            TextInput::make('customer_logged_out_label')
                                ->label('Signed out status label')
                                ->required()
                                ->maxLength(255),
                            TextInput::make('customer_basket_label')
                                ->label('Basket count label')
                                ->required()
                                ->maxLength(255),
                            Textarea::make('basket_membership_upsell')
                                ->label('Basket membership upsell')
                                ->helperText('Shown to signed-out visitors on the basket page. Links to the login page.')
                                ->required()
                                ->rows(2)
                                ->columnSpanFull(),
                        ]),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function save(PublicSiteCopyService $publicSiteCopy): void
    {
        /** @var array{
         *     listing_kicker: string,
         *     guide_price_label: string,
         *     guide_price_prefix: string,
         *     prices_confirmed_in_booking: string,
         *     dynamic_price_suffix: string,
         *     stale_price_suffix: string,
         *     performance_freshness_notice: string,
         *     booking_cta_label: string,
         *     online_booking_unavailable_label: string,
         *     secure_booking_prefix: string,
         *     footer_availability_notice: string,
         *     customer_logged_in_label: string,
         *     customer_logged_out_label: string,
         *     customer_basket_label: string,
         *     basket_membership_upsell: string
         * } $data
         */
        $data = $this->form->getState();

        $publicSiteCopy->update($data);

        Notification::make()
            ->success()
            ->title('Content strings updated')
            ->send();
    }
}
