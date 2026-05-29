<?php

declare(strict_types=1);

namespace App\Domains\Ticketing\Enums;

enum CustomerJourney: string
{
    case PasswordReset = 'password-reset';
    case Redeem = 'redeem';
    case Renew = 'renew';

    public function title(): string
    {
        return match ($this) {
            self::PasswordReset => 'Reset password',
            self::Redeem => 'Redeem a gift voucher',
            self::Renew => 'Renew membership',
        };
    }

    public function introduction(): string
    {
        return match ($this) {
            self::PasswordReset => 'Choose a new password for your ticketing account.',
            self::Redeem => 'Redeem a gift voucher securely through Spektrix.',
            self::Renew => 'Renew your membership securely through Spektrix.',
        };
    }

    public function backRouteName(): string
    {
        return match ($this) {
            self::Redeem, self::Renew => 'events.index',
            self::PasswordReset => 'ticketing.login',
        };
    }

    public function backLabel(): string
    {
        return match ($this) {
            self::Redeem, self::Renew => 'Back to events',
            self::PasswordReset => 'Back to log in',
        };
    }
}
