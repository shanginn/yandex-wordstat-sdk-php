<?php

declare(strict_types=1);

namespace Shanginn\YandexWordstat\Responses;

use Crell\Serde\Attributes as Serde;

final class DynamicsResult
{
    public function __construct(
        #[Serde\SequenceField(arrayType: DynamicsItem::class)]
        public array $dynamics
    ) {}
}
