<?php

declare(strict_types=1);

namespace Shanginn\YandexWordstat\Responses;

final class UserInfo
{
    public function __construct(
        public string $login,
        public int $limitPerSecond,
        public int $dailyLimit,
        public int $dailyLimitRemaining
    ) {}
}
