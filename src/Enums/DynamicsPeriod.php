<?php

declare(strict_types=1);

namespace Shanginn\YandexWordstat\Enums;

enum DynamicsPeriod: string
{
    case MONTHLY = 'monthly';
    case WEEKLY = 'weekly';
    case DAILY = 'daily';
}
