<?php

declare(strict_types=1);

namespace Shanginn\YandexWordstat\Requests;

use Crell\Serde\Attributes as Serde;

#[Serde\ClassSettings(omitNullFields: true)]
class TopRequestsRequest
{
    /**
     * @param string|null   $phrase   Single phrase (use either phrase or phrases, not both)
     * @param string[]|null $phrases  Array of phrases, max 128 (use either phrase or phrases, not both)
     * @param int[]|null    $regions
     * @param \Shanginn\YandexWordstat\Enums\DeviceType[]|string[]|null $devices
     */
    public function __construct(
        public ?string $phrase = null,
        public ?array $phrases = null,
        public ?int $numPhrases = null,
        public ?array $regions = null,
        public ?array $devices = null,
    ) {}
}
