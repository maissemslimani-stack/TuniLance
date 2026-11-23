<?php

namespace App\Enums;

enum ExperienceLevel: string
{
    case Entry = 'entry';
    case Intermediate = 'intermediate';
    case Expert = 'expert';

    public function label(): string
    {
        return match ($this) {
            self::Entry => 'Entry Level',
            self::Intermediate => 'Intermediate',
            self::Expert => 'Expert',
        };
    }
}