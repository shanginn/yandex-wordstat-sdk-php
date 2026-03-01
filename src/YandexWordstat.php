<?php

declare(strict_types=1);

namespace Shanginn\YandexWordstat;

use Shanginn\YandexWordstat\Enums\DeviceType;
use Shanginn\YandexWordstat\Enums\DynamicsPeriod;
use Shanginn\YandexWordstat\Enums\RegionType;
use Shanginn\YandexWordstat\Requests\TopRequestsRequest;
use Shanginn\YandexWordstat\Requests\DynamicsRequest;
use Shanginn\YandexWordstat\Requests\RegionsRequest;
use Shanginn\YandexWordstat\Responses\TopRequestsResult;
use Shanginn\YandexWordstat\Responses\DynamicsResult;
use Shanginn\YandexWordstat\Responses\RegionsResult;
use Shanginn\YandexWordstat\Responses\UserInfoResult;

class YandexWordstat
{
    private WordstatSerializer $serializer;

    public function __construct(
        private readonly WordstatClientInterface $client
    ) {
        $this->serializer = new WordstatSerializer();
    }

    /**
     * @return array Raw array describing the Yandex regions tree.
     */
    public function getRegionsTree(): array
    {
        $json = $this->client->post('getRegionsTree');
        return json_decode($json, true) ?? [];
    }

    /**
     * @param string|string[] $phrase  Single phrase or array of phrases (max 128)
     * @param int[]|null      $regions Region IDs to filter by
     * @param DeviceType[]|null $devices Device types to filter by
     * @return TopRequestsResult|TopRequestsResult[]
     */
    public function topRequests(
        string|array $phrase,
        ?int $numPhrases = null,
        ?array $regions = null,
        ?array $devices = null,
    ): array|TopRequestsResult {
        if (is_string($phrase)) {
            $request = new TopRequestsRequest(
                phrase: $phrase,
                numPhrases: $numPhrases,
                regions: $regions,
                devices: $devices,
            );
        } else {
            $request = new TopRequestsRequest(
                phrases: $phrase,
                numPhrases: $numPhrases,
                regions: $regions,
                devices: $devices,
            );
        }

        $json = $this->client->post('topRequests', $this->serializer->serialize($request));
        $data = json_decode($json, true);

        // Wordstat API returns array of objects when phrases (plural) was used
        if (is_array($data) && array_is_list($data)) {
            $results = [];
            foreach ($data as $item) {
                $results[] = $this->serializer->deserialize(json_encode($item), TopRequestsResult::class);
            }
            return $results;
        }

        return $this->serializer->deserialize($json, TopRequestsResult::class);
    }

    /**
     * @param int[]|null        $regions Region IDs to filter by
     * @param DeviceType[]|null $devices Device types to filter by
     */
    public function dynamics(
        string $phrase,
        DynamicsPeriod $period,
        string $fromDate,
        ?string $toDate = null,
        ?array $regions = null,
        ?array $devices = null,
    ): DynamicsResult {
        $request = new DynamicsRequest(
            phrase: $phrase,
            period: $period,
            fromDate: $fromDate,
            toDate: $toDate,
            regions: $regions,
            devices: $devices,
        );

        $json = $this->client->post('dynamics', $this->serializer->serialize($request));

        return $this->serializer->deserialize($json, DynamicsResult::class);
    }

    /**
     * @param DeviceType[]|null $devices Device types to filter by
     */
    public function regions(
        string $phrase,
        ?RegionType $regionType = null,
        ?array $devices = null,
    ): RegionsResult {
        $request = new RegionsRequest(
            phrase: $phrase,
            regionType: $regionType,
            devices: $devices,
        );

        $json = $this->client->post('regions', $this->serializer->serialize($request));

        return $this->serializer->deserialize($json, RegionsResult::class);
    }

    public function userInfo(): UserInfoResult
    {
        $json = $this->client->post('userInfo');

        return $this->serializer->deserialize($json, UserInfoResult::class);
    }
}
