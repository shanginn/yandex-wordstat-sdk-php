<?php

declare(strict_types=1);

namespace Shanginn\YandexWordstat;

use Crell\Serde\SerdeCommon;
use Shanginn\YandexWordstat\Exceptions\WordstatDeserializeException;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class WordstatSerializer
{
    private SerdeCommon $deserializer;
    private SerializerInterface $serializer;

    public function __construct()
    {
        $encoders = [new JsonEncoder()];
        // Excluded snake_case converter here because Wordstat API explicitly expects and returns camelCase values
        $normalizers = [
            new BackedEnumNormalizer(),
            new ObjectNormalizer(
                defaultContext: [AbstractObjectNormalizer::SKIP_NULL_VALUES => true]
            ),
        ];

        $this->serializer = new Serializer($normalizers, $encoders);
        $this->deserializer = new SerdeCommon();
    }

    public function serialize(mixed $data): string
    {
        return $this->serializer->serialize($data, 'json');
    }

    public function deserialize(mixed $serialized, string $to): object
    {
        try {
            return $this->deserializer->deserialize($serialized, 'json', $to);
        } catch (\Throwable $e) {
            throw new WordstatDeserializeException($serialized, $to, $e);
        }
    }
}
