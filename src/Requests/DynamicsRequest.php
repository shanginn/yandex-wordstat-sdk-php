<?php

declare(strict_types=1);

namespace Shanginn\YandexWordstat\Requests;

use Crell\Serde\Attributes as Serde;
use Shanginn\YandexWordstat\Enums\DynamicsPeriod;

#[Serde\ClassSettings(omitNullFields: true)]
class DynamicsRequest
{
    /**
     * @param int[]|null $regions
     * @param \Shanginn\YandexWordstat\Enums\DeviceType[]|string[]|null $devices
     */
    public function __construct(
        public string $phrase,
        public DynamicsPeriod $period,
        public string $fromDate,
        public ?string $toDate = null,
        public ?array $regions = null,
        public ?array $devices = null,
    ) {}
}
