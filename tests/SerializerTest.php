<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use Shanginn\YandexWordstat\WordstatSerializer;
use Shanginn\YandexWordstat\Requests\TopRequestsRequest;
use Shanginn\YandexWordstat\Requests\DynamicsRequest;
use Shanginn\YandexWordstat\Requests\RegionsRequest;
use Shanginn\YandexWordstat\Enums\DeviceType;
use Shanginn\YandexWordstat\Enums\DynamicsPeriod;
use Shanginn\YandexWordstat\Enums\RegionType;

/**
 * Tests that verify serialization matches Yandex Wordstat API requirements.
 * The Wordstat API expects camelCase field names.
 */
class SerializerTest extends TestCase
{
    private WordstatSerializer $serializer;

    protected function setUp(): void
    {
        $this->serializer = new WordstatSerializer();
    }

    public function testTopRequestsSerializesPhrase(): void
    {
        $request = new TopRequestsRequest(phrase: 'яндекс');
        $json = $this->serializer->serialize($request);
        $data = json_decode($json, true);

        $this->assertArrayHasKey('phrase', $data);
        $this->assertEquals('яндекс', $data['phrase']);
    }

    public function testTopRequestsSerializesArrayPhrase(): void
    {
        $request = new TopRequestsRequest(phrase: ['яндекс', 'гугл']);
        $json = $this->serializer->serialize($request);
        $data = json_decode($json, true);

        $this->assertIsArray($data['phrase']);
        $this->assertEquals(['яндекс', 'гугл'], $data['phrase']);
    }

    public function testTopRequestsOmitsNullFields(): void
    {
        $request = new TopRequestsRequest(phrase: 'яндекс');
        $json = $this->serializer->serialize($request);
        $data = json_decode($json, true);

        $this->assertArrayNotHasKey('limit', $data);
        $this->assertArrayNotHasKey('regions', $data);
        $this->assertArrayNotHasKey('devices', $data);
    }

    public function testTopRequestsSerializesAllFields(): void
    {
        $request = new TopRequestsRequest(
            phrase: 'яндекс',
            limit: 50,
            regions: [213, 2],
            devices: [DeviceType::PHONE, DeviceType::TABLET],
        );
        $json = $this->serializer->serialize($request);
        $data = json_decode($json, true);

        $this->assertEquals('яндекс', $data['phrase']);
        $this->assertEquals(50, $data['limit']);
        $this->assertEquals([213, 2], $data['regions']);
        $this->assertEquals(['phone', 'tablet'], $data['devices']);
    }

    public function testDynamicsSerializesCamelCase(): void
    {
        $request = new DynamicsRequest(
            phrase: 'яндекс',
            period: DynamicsPeriod::MONTHLY,
            fromDate: '2025-01-01',
            toDate: '2025-03-31',
        );
        $json = $this->serializer->serialize($request);
        $data = json_decode($json, true);

        // Wordstat API expects camelCase: fromDate, toDate
        $this->assertArrayHasKey('fromDate', $data);
        $this->assertArrayHasKey('toDate', $data);
        $this->assertArrayNotHasKey('from_date', $data);
        $this->assertArrayNotHasKey('to_date', $data);
        $this->assertEquals('2025-01-01', $data['fromDate']);
        $this->assertEquals('2025-03-31', $data['toDate']);
    }

    public function testDynamicsOmitsToDateWhenNull(): void
    {
        $request = new DynamicsRequest(
            phrase: 'яндекс',
            period: DynamicsPeriod::WEEKLY,
            fromDate: '2025-01-01',
        );
        $json = $this->serializer->serialize($request);
        $data = json_decode($json, true);

        $this->assertArrayHasKey('fromDate', $data);
        $this->assertArrayNotHasKey('toDate', $data);
    }

    public function testDynamicsPeriodEnumSerializes(): void
    {
        foreach ([
            [DynamicsPeriod::MONTHLY, 'monthly'],
            [DynamicsPeriod::WEEKLY, 'weekly'],
            [DynamicsPeriod::DAILY, 'daily'],
        ] as [$enum, $expected]) {
            $request = new DynamicsRequest(
                phrase: 'тест',
                period: $enum,
                fromDate: '2025-01-01',
            );
            $data = json_decode($this->serializer->serialize($request), true);
            $this->assertEquals($expected, $data['period']);
        }
    }

    public function testRegionsSerializesRegionType(): void
    {
        $request = new RegionsRequest(
            phrase: 'яндекс',
            regionType: RegionType::CITIES,
        );
        $json = $this->serializer->serialize($request);
        $data = json_decode($json, true);

        $this->assertArrayHasKey('regionType', $data);
        $this->assertArrayNotHasKey('region_type', $data);
        $this->assertEquals('cities', $data['regionType']);
    }

    public function testRegionsOmitsNullFields(): void
    {
        $request = new RegionsRequest(phrase: 'яндекс');
        $json = $this->serializer->serialize($request);
        $data = json_decode($json, true);

        $this->assertArrayNotHasKey('regionType', $data);
        $this->assertArrayNotHasKey('devices', $data);
    }

    public function testDeviceTypeEnumSerializes(): void
    {
        foreach ([
            [DeviceType::ALL, 'all'],
            [DeviceType::DESKTOP, 'desktop'],
            [DeviceType::MOBILE, 'mobile'],
            [DeviceType::PHONE, 'phone'],
            [DeviceType::TABLET, 'tablet'],
        ] as [$enum, $expected]) {
            $request = new TopRequestsRequest(phrase: 'тест', devices: [$enum]);
            $data = json_decode($this->serializer->serialize($request), true);
            $this->assertEquals([$expected], $data['devices']);
        }
    }

    public function testRegionTypeEnumSerializes(): void
    {
        foreach ([
            [RegionType::ANY, 'any'],
            [RegionType::CITIES, 'cities'],
            [RegionType::REGIONS, 'regions'],
            [RegionType::EVERYWHERE, 'everywhere'],
        ] as [$enum, $expected]) {
            $request = new RegionsRequest(phrase: 'тест', regionType: $enum);
            $data = json_decode($this->serializer->serialize($request), true);
            $this->assertEquals($expected, $data['regionType']);
        }
    }
}
