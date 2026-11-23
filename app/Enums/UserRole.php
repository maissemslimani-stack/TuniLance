<?php

namespace App\Enums;

enum UserRole: string
{
    case Client     = 'client';
    case Freelancer = 'freelancer';
    case Moderator  = 'moderator';

    public function label(): string
    {
        return match($this) {
            self::Client     => 'Client',
            self::Freelancer => 'Freelancer',
            self::Moderator  => 'Moderator',
        };
    }
}
