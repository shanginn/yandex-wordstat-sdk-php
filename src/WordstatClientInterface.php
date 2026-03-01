<?php

declare(strict_types=1);

namespace Shanginn\YandexWordstat;

interface WordstatClientInterface
{
    public function post(string $endpoint, string $jsonBody = ''): string;
}
