<?php

declare(strict_types=1);

namespace Shanginn\YandexWordstat\Responses;

use Crell\Serde\Attributes as Serde;

final class TopRequestsResult
{
    public function __construct(
        public string $requestPhrase,
        public ?int $totalCount = null,
        #[Serde\SequenceField(arrayType: PhraseCount::class)]
        public ?array $topRequests = null,
        #[Serde\SequenceField(arrayType: PhraseCount::class)]
        public ?array $associations = null,
        public ?string $error = null
    ) {}
}
