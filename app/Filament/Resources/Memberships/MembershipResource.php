<?php

declare(strict_types=1);

namespace App\Filament\Resources\Memberships;

use App\Domains\CMS\Models\Membership;
use App\Filament\Resources\Memberships\Pages\EditMembership;
use App\Filament\Resources\Memberships\Pages\ListMemberships;
use App\Filament\Resources\Memberships\Pages\ViewMembership;
use App\Filament\Resources\Memberships\Schemas\MembershipForm;
use App\Filament\Resources\Memberships\Schemas\MembershipInfolist;
use App\Filament\Resources\Memberships\Tables\MembershipsTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MembershipResource extends Resource
{
    protected static ?string $model = Membership::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = 'Ticketing';

    protected static ?string $navigationLabel = 'Memberships';

    protected static ?string $modelLabel = 'membership';

    protected static ?string $pluralModelLabel = 'memberships';

    protected static ?int $navigationSort = 41;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return MembershipForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MembershipInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MembershipsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMemberships::route('/'),
            'view' => ViewMembership::route('/{record}'),
            'edit' => EditMembership::route('/{record}/edit'),
        ];
    }
}
