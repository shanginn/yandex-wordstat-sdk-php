<?php

declare(strict_types=1);

namespace Shanginn\YandexWordstat;

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
     * @return TopRequestsResult|TopRequestsResult[]
     */
    public function topRequests(TopRequestsRequest $request): array|TopRequestsResult
    {
        $json = $this->client->post('topRequests', $this->serializer->serialize($request));
        $data = json_decode($json, true);

        // Wordstat API returns array of objects if request phrase was passed as an array
        if (is_array($data) && array_is_list($data)) {
            $results = [];
            foreach ($data as $item) {
                $results[] = $this->serializer->deserialize(json_encode($item), TopRequestsResult::class);
            }
            return $results;
        }

        return $this->serializer->deserialize($json, TopRequestsResult::class);
    }

    public function dynamics(DynamicsRequest $request): DynamicsResult
    {
        $json = $this->client->post('dynamics', $this->serializer->serialize($request));

        return $this->serializer->deserialize($json, DynamicsResult::class);
    }

    public function regions(RegionsRequest $request): RegionsResult
    {
        $json = $this->client->post('regions', $this->serializer->serialize($request));

        return $this->serializer->deserialize($json, RegionsResult::class);
    }

    public function userInfo(): UserInfoResult
    {
        $json = $this->client->post('userInfo');

        return $this->serializer->deserialize($json, UserInfoResult::class);
    }
}
