<?php

declare(strict_types=1);

namespace Shanginn\YandexWordstat\Enums;

enum DeviceType: string
{
    case ALL = 'all';
    case DESKTOP = 'desktop';
    case MOBILE = 'mobile';
    case PHONE = 'phone';
    case TABLET = 'tablet';
}
