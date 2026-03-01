<?php

declare(strict_types=1);

namespace Shanginn\YandexWordstat\Responses;

final class DynamicsItem
{
    public function __construct(
        public string $date,
        public int $count,
        public float $share
    ) {}
}
