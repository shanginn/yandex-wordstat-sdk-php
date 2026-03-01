<?php

declare(strict_types=1);

namespace Shanginn\YandexWordstat\Responses;

use Crell\Serde\Attributes as Serde;

final class RegionsResult
{
    public function __construct(
        #[Serde\SequenceField(arrayType: RegionItem::class)]
        public array $regions
    ) {}
}
