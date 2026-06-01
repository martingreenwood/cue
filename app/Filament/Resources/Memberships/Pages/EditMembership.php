<?php

declare(strict_types=1);

namespace App\Filament\Resources\Memberships\Pages;

use App\Filament\Resources\Memberships\MembershipResource;
use Filament\Resources\Pages\EditRecord;

class EditMembership extends EditRecord
{
    protected static string $resource = MembershipResource::class;
}
