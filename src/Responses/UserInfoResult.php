<?php

declare(strict_types=1);

namespace Shanginn\YandexWordstat\Responses;

final class UserInfoResult
{
    public function __construct(
        public UserInfo $userInfo
    ) {}
}
