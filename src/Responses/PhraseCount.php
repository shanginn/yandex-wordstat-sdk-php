<?php

declare(strict_types=1);

namespace Shanginn\YandexWordstat\Responses;

final class PhraseCount
{
    public function __construct(
        public string $phrase,
        public int $count
    ) {}
}
