<?php

declare(strict_types=1);

namespace Tests;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Shanginn\YandexWordstat\YandexWordstat;
use Shanginn\YandexWordstat\WordstatClientInterface;
use Shanginn\YandexWordstat\Requests\TopRequestsRequest;
use Shanginn\YandexWordstat\Requests\DynamicsRequest;
use Shanginn\YandexWordstat\Requests\RegionsRequest;
use Shanginn\YandexWordstat\Responses\TopRequestsResult;
use Shanginn\YandexWordstat\Responses\DynamicsResult;
use Shanginn\YandexWordstat\Responses\RegionsResult;
use Shanginn\YandexWordstat\Responses\UserInfoResult;
use Shanginn\YandexWordstat\Enums\DeviceType;
use Shanginn\YandexWordstat\Enums\DynamicsPeriod;
use Shanginn\YandexWordstat\Enums\RegionType;

class YandexWordstatTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private $mockClient;
    private YandexWordstat $wordstat;

    protected function setUp(): void
    {
        $this->mockClient = Mockery::mock(WordstatClientInterface::class);
        $this->wordstat = new YandexWordstat($this->mockClient);
    }

    public function testTopRequestsSuccess(): void
    {
        $response = json_encode([
            'requestPhrase' => 'яндекс',
            'totalCount' => 1000000,
            'topRequests' => [
                ['phrase' => 'яндекс почта', 'count' => 500000],
                ['phrase' => 'яндекс карты', 'count' => 300000],
            ],
            'associations' => [
                ['phrase' => 'гугл', 'count' => 200000],
            ],
        ]);

        $this->mockClient
            ->shouldReceive('post')
            ->once()
            ->withArgs(function ($endpoint, $body) {
                $data = json_decode($body, true);
                return $endpoint === 'topRequests' && $data['phrase'] === 'яндекс';
            })
            ->andReturn($response);

        $result = $this->wordstat->topRequests(new TopRequestsRequest(phrase: 'яндекс'));

        $this->assertInstanceOf(TopRequestsResult::class, $result);
        $this->assertEquals('яндекс', $result->requestPhrase);
        $this->assertEquals(1000000, $result->totalCount);
        $this->assertCount(2, $result->topRequests);
        $this->assertEquals('яндекс почта', $result->topRequests[0]->phrase);
        $this->assertEquals(500000, $result->topRequests[0]->count);
        $this->assertCount(1, $result->associations);
    }

    public function testTopRequestsWithArrayPhraseReturnsArray(): void
    {
        $response = json_encode([
            [
                'requestPhrase' => 'яндекс',
                'totalCount' => 1000000,
                'topRequests' => [['phrase' => 'яндекс почта', 'count' => 500000]],
            ],
            [
                'requestPhrase' => 'гугл',
                'totalCount' => 800000,
                'topRequests' => [['phrase' => 'гугл карты', 'count' => 400000]],
            ],
        ]);

        $this->mockClient
            ->shouldReceive('post')
            ->once()
            ->withArgs(function ($endpoint, $body) {
                $data = json_decode($body, true);
                return $endpoint === 'topRequests' && is_array($data['phrase']);
            })
            ->andReturn($response);

        $results = $this->wordstat->topRequests(new TopRequestsRequest(phrase: ['яндекс', 'гугл']));

        $this->assertIsArray($results);
        $this->assertCount(2, $results);
        $this->assertInstanceOf(TopRequestsResult::class, $results[0]);
        $this->assertEquals('яндекс', $results[0]->requestPhrase);
        $this->assertEquals('гугл', $results[1]->requestPhrase);
    }

    public function testTopRequestsWithDeviceAndRegionFilters(): void
    {
        $response = json_encode([
            'requestPhrase' => 'яндекс',
            'totalCount' => 500000,
            'topRequests' => [],
        ]);

        $this->mockClient
            ->shouldReceive('post')
            ->once()
            ->withArgs(function ($endpoint, $body) {
                $data = json_decode($body, true);
                return $endpoint === 'topRequests'
                    && $data['phrase'] === 'яндекс'
                    && $data['regions'] === [213, 2]
                    && $data['devices'] === ['phone']
                    && $data['limit'] === 10;
            })
            ->andReturn($response);

        $result = $this->wordstat->topRequests(new TopRequestsRequest(
            phrase: 'яндекс',
            limit: 10,
            regions: [213, 2],
            devices: [DeviceType::PHONE],
        ));

        $this->assertInstanceOf(TopRequestsResult::class, $result);
        $this->assertEquals(500000, $result->totalCount);
    }

    public function testTopRequestsWithNoResults(): void
    {
        $response = json_encode([
            'requestPhrase' => 'xyzxyzxyz',
            'totalCount' => 0,
            'topRequests' => [],
        ]);

        $this->mockClient
            ->shouldReceive('post')
            ->once()
            ->andReturn($response);

        $result = $this->wordstat->topRequests(new TopRequestsRequest(phrase: 'xyzxyzxyz'));

        $this->assertEquals(0, $result->totalCount);
        $this->assertEmpty($result->topRequests);
    }

    public function testDynamicsSuccess(): void
    {
        $response = json_encode([
            'dynamics' => [
                ['date' => '2025-01-01', 'count' => 10000, 'share' => 1.5],
                ['date' => '2025-02-01', 'count' => 12000, 'share' => 1.8],
                ['date' => '2025-03-01', 'count' => 11000, 'share' => 1.6],
            ],
        ]);

        $this->mockClient
            ->shouldReceive('post')
            ->once()
            ->withArgs(function ($endpoint, $body) {
                $data = json_decode($body, true);
                return $endpoint === 'dynamics'
                    && $data['phrase'] === 'яндекс'
                    && $data['period'] === 'monthly'
                    && $data['fromDate'] === '2025-01-01'
                    && $data['toDate'] === '2025-03-31';
            })
            ->andReturn($response);

        $result = $this->wordstat->dynamics(new DynamicsRequest(
            phrase: 'яндекс',
            period: DynamicsPeriod::MONTHLY,
            fromDate: '2025-01-01',
            toDate: '2025-03-31',
        ));

        $this->assertInstanceOf(DynamicsResult::class, $result);
        $this->assertCount(3, $result->dynamics);
        $this->assertEquals('2025-01-01', $result->dynamics[0]->date);
        $this->assertEquals(10000, $result->dynamics[0]->count);
        $this->assertEquals(1.5, $result->dynamics[0]->share);
    }

    public function testDynamicsWithoutToDate(): void
    {
        $response = json_encode([
            'dynamics' => [
                ['date' => '2025-01-01', 'count' => 10000, 'share' => 1.5],
            ],
        ]);

        $this->mockClient
            ->shouldReceive('post')
            ->once()
            ->withArgs(function ($endpoint, $body) {
                $data = json_decode($body, true);
                return $endpoint === 'dynamics'
                    && $data['phrase'] === 'яндекс'
                    && $data['period'] === 'weekly'
                    && $data['fromDate'] === '2025-01-01'
                    && !isset($data['toDate']);
            })
            ->andReturn($response);

        $result = $this->wordstat->dynamics(new DynamicsRequest(
            phrase: 'яндекс',
            period: DynamicsPeriod::WEEKLY,
            fromDate: '2025-01-01',
        ));

        $this->assertInstanceOf(DynamicsResult::class, $result);
        $this->assertCount(1, $result->dynamics);
    }

    public function testRegionsSuccess(): void
    {
        $response = json_encode([
            'regions' => [
                ['regionId' => 213, 'count' => 500000, 'share' => 10.5, 'affinityIndex' => 1.2],
                ['regionId' => 2, 'count' => 300000, 'share' => 6.3, 'affinityIndex' => 0.9],
                ['regionId' => 66, 'count' => 100000, 'share' => 2.1, 'affinityIndex' => 0.8],
            ],
        ]);

        $this->mockClient
            ->shouldReceive('post')
            ->once()
            ->withArgs(function ($endpoint, $body) {
                $data = json_decode($body, true);
                return $endpoint === 'regions'
                    && $data['phrase'] === 'яндекс'
                    && $data['regionType'] === 'cities';
            })
            ->andReturn($response);

        $result = $this->wordstat->regions(new RegionsRequest(
            phrase: 'яндекс',
            regionType: RegionType::CITIES,
        ));

        $this->assertInstanceOf(RegionsResult::class, $result);
        $this->assertCount(3, $result->regions);
        $this->assertEquals(213, $result->regions[0]->regionId);
        $this->assertEquals(500000, $result->regions[0]->count);
        $this->assertEquals(10.5, $result->regions[0]->share);
        $this->assertEquals(1.2, $result->regions[0]->affinityIndex);
    }

    public function testRegionsWithDeviceFilter(): void
    {
        $response = json_encode([
            'regions' => [
                ['regionId' => 213, 'count' => 200000, 'share' => 15.0, 'affinityIndex' => 1.5],
            ],
        ]);

        $this->mockClient
            ->shouldReceive('post')
            ->once()
            ->withArgs(function ($endpoint, $body) {
                $data = json_decode($body, true);
                return $endpoint === 'regions'
                    && $data['phrase'] === 'яндекс'
                    && $data['devices'] === ['mobile'];
            })
            ->andReturn($response);

        $result = $this->wordstat->regions(new RegionsRequest(
            phrase: 'яндекс',
            devices: [DeviceType::MOBILE],
        ));

        $this->assertInstanceOf(RegionsResult::class, $result);
        $this->assertCount(1, $result->regions);
    }

    public function testUserInfoSuccess(): void
    {
        $response = json_encode([
            'userInfo' => [
                'login' => 'testuser',
                'limitPerSecond' => 10,
                'dailyLimit' => 10000,
                'dailyLimitRemaining' => 9500,
            ],
        ]);

        $this->mockClient
            ->shouldReceive('post')
            ->once()
            ->with('userInfo')
            ->andReturn($response);

        $result = $this->wordstat->userInfo();

        $this->assertInstanceOf(UserInfoResult::class, $result);
        $this->assertEquals('testuser', $result->userInfo->login);
        $this->assertEquals(10, $result->userInfo->limitPerSecond);
        $this->assertEquals(10000, $result->userInfo->dailyLimit);
        $this->assertEquals(9500, $result->userInfo->dailyLimitRemaining);
    }

    public function testGetRegionsTreeReturnsArray(): void
    {
        $response = json_encode([
            ['id' => 225, 'name' => 'Россия', 'children' => [
                ['id' => 213, 'name' => 'Москва', 'children' => []],
                ['id' => 2, 'name' => 'Санкт-Петербург', 'children' => []],
            ]],
        ]);

        $this->mockClient
            ->shouldReceive('post')
            ->once()
            ->with('getRegionsTree')
            ->andReturn($response);

        $result = $this->wordstat->getRegionsTree();

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals(225, $result[0]['id']);
        $this->assertEquals('Россия', $result[0]['name']);
        $this->assertCount(2, $result[0]['children']);
    }
}
