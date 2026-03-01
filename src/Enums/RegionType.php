<?php

declare(strict_types=1);

namespace Shanginn\YandexWordstat\Enums;

enum RegionType: string
{
    case ANY = 'any';
    case CITIES = 'cities';
    case REGIONS = 'regions';
    case EVERYWHERE = 'everywhere';
}
