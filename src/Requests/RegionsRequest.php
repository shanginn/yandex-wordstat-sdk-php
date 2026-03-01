<?php

declare(strict_types=1);

namespace Shanginn\YandexWordstat\Requests;

use Crell\Serde\Attributes as Serde;
use Shanginn\YandexWordstat\Enums\RegionType;

#[Serde\ClassSettings(omitNullFields: true)]
class RegionsRequest
{
    /**
     * @param \Shanginn\YandexWordstat\Enums\DeviceType[]|string[]|null $devices
     */
    public function __construct(
        public string $phrase,
        public ?RegionType $regionType = null,
        public ?array $devices = null,
    ) {}
}
