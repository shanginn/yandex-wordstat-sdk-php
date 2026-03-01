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

/**
 * Tests for wiki code examples to ensure they work correctly.
 * These tests verify that all code examples from the wiki documentation
 * execute without errors and produce expected results.
 */
class WikiCodeExamplesTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private $mockClient;
    private YandexWordstat $wordstat;

    protected function setUp(): void
    {
        $this->mockClient = Mockery::mock(WordstatClientInterface::class);
        $this->wordstat = new YandexWordstat($this->mockClient);
    }

    // ==========================================
    // Quick Start Example
    // ==========================================

    public function testQuickStartUserInfoExample(): void
    {
        $response = json_encode([
            'userInfo' => [
                'login' => 'mylogin',
                'limitPerSecond' => 10,
                'dailyLimit' => 10000,
                'dailyLimitRemaining' => 9800,
            ],
        ]);

        $this->mockClient
            ->shouldReceive('post')
            ->once()
            ->with('userInfo')
            ->andReturn($response);

        $userInfo = $this->wordstat->userInfo();

        // Wiki example: $wordstat->userInfo()->userInfo->login
        $this->assertEquals('mylogin', $userInfo->userInfo->login);
        $this->assertEquals(9800, $userInfo->userInfo->dailyLimitRemaining);
    }

    // ==========================================
    // /topRequests Endpoint Tests
    // ==========================================

    public function testTopRequestsBasicExample(): void
    {
        $response = json_encode([
            'requestPhrase' => 'яндекс',
            'totalCount' => 5000000,
            'topRequests' => [
                ['phrase' => 'яндекс почта', 'count' => 2000000],
                ['phrase' => 'яндекс карты', 'count' => 1500000],
            ],
            'associations' => [],
        ]);

        $this->mockClient
            ->shouldReceive('post')
            ->once()
            ->withArgs(function ($endpoint, $body) {
                $data = json_decode($body, true);
                return $endpoint === 'topRequests' && $data['phrase'] === 'яндекс';
            })
            ->andReturn($response);

        $result = $this->wordstat->topRequests(new TopRequestsRequest(
            phrase: 'яндекс',
        ));

        $this->assertInstanceOf(TopRequestsResult::class, $result);
        $this->assertEquals(5000000, $result->totalCount);
        $this->assertCount(2, $result->topRequests);
    }

    public function testTopRequestsWithLimitExample(): void
    {
        $response = json_encode([
            'requestPhrase' => 'яндекс',
            'totalCount' => 5000000,
            'topRequests' => [
                ['phrase' => 'яндекс почта', 'count' => 2000000],
            ],
        ]);

        $this->mockClient
            ->shouldReceive('post')
            ->once()
            ->withArgs(function ($endpoint, $body) {
                $data = json_decode($body, true);
                return $endpoint === 'topRequests'
                    && $data['phrase'] === 'яндекс'
                    && $data['limit'] === 10;
            })
            ->andReturn($response);

        $result = $this->wordstat->topRequests(new TopRequestsRequest(
            phrase: 'яндекс',
            limit: 10,
        ));

        $this->assertEquals('яндекс', $result->requestPhrase);
    }

    public function testTopRequestsWithMoscowRegionExample(): void
    {
        $response = json_encode([
            'requestPhrase' => 'яндекс',
            'totalCount' => 1000000,
            'topRequests' => [
                ['phrase' => 'яндекс почта', 'count' => 500000],
            ],
        ]);

        $this->mockClient
            ->shouldReceive('post')
            ->once()
            ->withArgs(function ($endpoint, $body) {
                $data = json_decode($body, true);
                return $endpoint === 'topRequests'
                    && $data['phrase'] === 'яндекс'
                    && $data['regions'] === [213];
            })
            ->andReturn($response);

        // Wiki example: filter by Moscow (region ID 213)
        $result = $this->wordstat->topRequests(new TopRequestsRequest(
            phrase: 'яндекс',
            regions: [213],
        ));

        $this->assertInstanceOf(TopRequestsResult::class, $result);
    }

    public function testTopRequestsMobileOnlyExample(): void
    {
        $response = json_encode([
            'requestPhrase' => 'яндекс',
            'totalCount' => 3000000,
            'topRequests' => [],
        ]);

        $this->mockClient
            ->shouldReceive('post')
            ->once()
            ->withArgs(function ($endpoint, $body) {
                $data = json_decode($body, true);
                return $endpoint === 'topRequests'
                    && $data['devices'] === ['phone'];
            })
            ->andReturn($response);

        $result = $this->wordstat->topRequests(new TopRequestsRequest(
            phrase: 'яндекс',
            devices: [DeviceType::PHONE],
        ));

        $this->assertInstanceOf(TopRequestsResult::class, $result);
    }

    public function testTopRequestsMultiplePhrasesExample(): void
    {
        $response = json_encode([
            [
                'requestPhrase' => 'яндекс',
                'totalCount' => 5000000,
                'topRequests' => [['phrase' => 'яндекс почта', 'count' => 2000000]],
            ],
            [
                'requestPhrase' => 'гугл',
                'totalCount' => 4000000,
                'topRequests' => [['phrase' => 'гугл хром', 'count' => 1500000]],
            ],
        ]);

        $this->mockClient
            ->shouldReceive('post')
            ->once()
            ->withArgs(function ($endpoint, $body) {
                $data = json_decode($body, true);
                return $endpoint === 'topRequests'
                    && $data['phrase'] === ['яндекс', 'гугл'];
            })
            ->andReturn($response);

        // Wiki example: batch request for multiple phrases
        $results = $this->wordstat->topRequests(new TopRequestsRequest(
            phrase: ['яндекс', 'гугл'],
        ));

        $this->assertIsArray($results);
        $this->assertCount(2, $results);
        $this->assertEquals('яндекс', $results[0]->requestPhrase);
        $this->assertEquals('гугл', $results[1]->requestPhrase);
    }

    // ==========================================
    // /dynamics Endpoint Tests
    // ==========================================

    public function testDynamicsMonthlyExample(): void
    {
        $response = json_encode([
            'dynamics' => [
                ['date' => '2025-01-01', 'count' => 10000, 'share' => 1.5],
                ['date' => '2025-02-01', 'count' => 12000, 'share' => 1.8],
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
                    && $data['toDate'] === '2025-02-28';
            })
            ->andReturn($response);

        $dynamics = $this->wordstat->dynamics(new DynamicsRequest(
            phrase: 'яндекс',
            period: DynamicsPeriod::MONTHLY,
            fromDate: '2025-01-01',
            toDate: '2025-02-28',
        ));

        $this->assertInstanceOf(DynamicsResult::class, $dynamics);
        $this->assertCount(2, $dynamics->dynamics);
        $this->assertEquals('2025-01-01', $dynamics->dynamics[0]->date);
        $this->assertEquals(10000, $dynamics->dynamics[0]->count);
    }

    public function testDynamicsWeeklyExample(): void
    {
        $response = json_encode([
            'dynamics' => [
                ['date' => '2025-01-06', 'count' => 2500, 'share' => 0.4],
                ['date' => '2025-01-13', 'count' => 2700, 'share' => 0.4],
            ],
        ]);

        $this->mockClient
            ->shouldReceive('post')
            ->once()
            ->withArgs(function ($endpoint, $body) {
                $data = json_decode($body, true);
                return $endpoint === 'dynamics' && $data['period'] === 'weekly';
            })
            ->andReturn($response);

        $dynamics = $this->wordstat->dynamics(new DynamicsRequest(
            phrase: 'яндекс',
            period: DynamicsPeriod::WEEKLY,
            fromDate: '2025-01-01',
            toDate: '2025-01-31',
        ));

        $this->assertCount(2, $dynamics->dynamics);
    }

    public function testDynamicsDailyExample(): void
    {
        $response = json_encode([
            'dynamics' => [
                ['date' => '2025-01-01', 'count' => 350, 'share' => 0.05],
            ],
        ]);

        $this->mockClient
            ->shouldReceive('post')
            ->once()
            ->withArgs(function ($endpoint, $body) {
                $data = json_decode($body, true);
                return $endpoint === 'dynamics' && $data['period'] === 'daily';
            })
            ->andReturn($response);

        $dynamics = $this->wordstat->dynamics(new DynamicsRequest(
            phrase: 'яндекс',
            period: DynamicsPeriod::DAILY,
            fromDate: '2025-01-01',
            toDate: '2025-01-07',
        ));

        $this->assertCount(1, $dynamics->dynamics);
        $this->assertEquals(0.05, $dynamics->dynamics[0]->share);
    }

    public function testDynamicsWithDeviceFilterExample(): void
    {
        $response = json_encode([
            'dynamics' => [
                ['date' => '2025-01-01', 'count' => 5000, 'share' => 0.8],
            ],
        ]);

        $this->mockClient
            ->shouldReceive('post')
            ->once()
            ->withArgs(function ($endpoint, $body) {
                $data = json_decode($body, true);
                return $endpoint === 'dynamics'
                    && $data['devices'] === ['desktop'];
            })
            ->andReturn($response);

        $dynamics = $this->wordstat->dynamics(new DynamicsRequest(
            phrase: 'яндекс',
            period: DynamicsPeriod::MONTHLY,
            fromDate: '2025-01-01',
            devices: [DeviceType::DESKTOP],
        ));

        $this->assertInstanceOf(DynamicsResult::class, $dynamics);
    }

    // ==========================================
    // /regions Endpoint Tests
    // ==========================================

    public function testRegionsCitiesExample(): void
    {
        $response = json_encode([
            'regions' => [
                ['regionId' => 213, 'count' => 1000000, 'share' => 20.5, 'affinityIndex' => 1.3],
                ['regionId' => 2, 'count' => 500000, 'share' => 10.2, 'affinityIndex' => 0.9],
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
        $this->assertCount(2, $result->regions);
        $this->assertEquals(213, $result->regions[0]->regionId);
        $this->assertEquals(1.3, $result->regions[0]->affinityIndex);
    }

    public function testRegionsAllTypesExample(): void
    {
        $response = json_encode([
            'regions' => [
                ['regionId' => 225, 'count' => 5000000, 'share' => 100.0, 'affinityIndex' => 1.0],
            ],
        ]);

        $this->mockClient
            ->shouldReceive('post')
            ->once()
            ->withArgs(function ($endpoint, $body) {
                $data = json_decode($body, true);
                return $endpoint === 'regions'
                    && !isset($data['regionType']);
            })
            ->andReturn($response);

        // Wiki example: no regionType filter
        $result = $this->wordstat->regions(new RegionsRequest(phrase: 'яндекс'));

        $this->assertInstanceOf(RegionsResult::class, $result);
    }

    // ==========================================
    // /getRegionsTree Endpoint Tests
    // ==========================================

    public function testGetRegionsTreeExample(): void
    {
        $response = json_encode([
            [
                'id' => 225,
                'name' => 'Россия',
                'children' => [
                    ['id' => 1, 'name' => 'Москва и Московская область', 'children' => [
                        ['id' => 213, 'name' => 'Москва', 'children' => []],
                    ]],
                    ['id' => 10174, 'name' => 'Санкт-Петербург и Ленинградская область', 'children' => [
                        ['id' => 2, 'name' => 'Санкт-Петербург', 'children' => []],
                    ]],
                ],
            ],
        ]);

        $this->mockClient
            ->shouldReceive('post')
            ->once()
            ->with('getRegionsTree')
            ->andReturn($response);

        $tree = $this->wordstat->getRegionsTree();

        $this->assertIsArray($tree);
        $this->assertEquals(225, $tree[0]['id']);
        $this->assertEquals('Россия', $tree[0]['name']);
        $this->assertCount(2, $tree[0]['children']);
    }
}
