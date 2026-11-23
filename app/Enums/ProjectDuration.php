<?php

namespace App\Enums;

enum ProjectDuration: string
{
    case LessThanMonth = 'less_than_month';
    case OneToThreeMonths = 'one_to_three_months';
    case ThreePlusMonths = 'three_plus_months';

    public function label(): string
    {
        return match ($this) {
            self::LessThanMonth => 'Less than 1 month',
            self::OneToThreeMonths => '1 - 3 months',
            self::ThreePlusMonths => '3+ months',
        };
    }
}