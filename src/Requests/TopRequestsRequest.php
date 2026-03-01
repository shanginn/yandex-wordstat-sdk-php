<?php

declare(strict_types=1);

namespace Shanginn\YandexWordstat\Requests;

use Crell\Serde\Attributes as Serde;

#[Serde\ClassSettings(omitNullFields: true)]
class TopRequestsRequest
{
    /**
     * @param string|string[] $phrase
     * @param int[]|null $regions
     * @param \Shanginn\YandexWordstat\Enums\DeviceType[]|string[]|null $devices
     */
    public function __construct(
        public string|array $phrase,
        public ?int $limit = null,
        public ?array $regions = null,
        public ?array $devices = null,
    ) {}
}
