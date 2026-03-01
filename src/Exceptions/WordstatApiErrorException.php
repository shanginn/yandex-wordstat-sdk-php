<?php

declare(strict_types=1);

namespace Shanginn\YandexWordstat\Exceptions;

class WordstatApiErrorException extends WordstatException
{
    public function __construct(string $message, public int $statusCode = 0)
    {
        parent::__construct("Yandex Wordstat API Error: {$message}", $statusCode);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
