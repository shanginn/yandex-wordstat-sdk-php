<?php

declare(strict_types=1);

namespace Shanginn\YandexWordstat\Exceptions;

class WordstatRateLimitException extends WordstatApiErrorException
{
    public function __construct(string $message = 'Rate limit exceeded')
    {
        parent::__construct($message, 429);
    }
}
