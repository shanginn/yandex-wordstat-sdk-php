<?php

declare(strict_types=1);

namespace Shanginn\YandexWordstat\Responses;

final class RegionItem
{
    public function __construct(
        public int $regionId,
        public int $count,
        public float $share,
        public float $affinityIndex
    ) {}
}
