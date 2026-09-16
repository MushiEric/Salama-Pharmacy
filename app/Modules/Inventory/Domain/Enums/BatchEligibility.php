<?php

namespace App\Modules\Inventory\Domain\Enums;

enum BatchEligibility: string
{
    case Eligible = 'eligible';
    case Warning = 'warning';
    case Blocked = 'blocked';
}
