<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Shanginn\YandexWordstat\WordstatClient;
use Shanginn\YandexWordstat\YandexWordstat;
use Shanginn\YandexWordstat\Requests\TopRequestsRequest;
use Shanginn\YandexWordstat\Requests\DynamicsRequest;
use Shanginn\YandexWordstat\Requests\RegionsRequest;
use Shanginn\YandexWordstat\Enums\DynamicsPeriod;
use Shanginn\YandexWordstat\Enums\DeviceType;
use Shanginn\YandexWordstat\Enums\RegionType;

$oauthToken = getenv('YANDEX_OAUTH_TOKEN');

if (empty($oauthToken)) {
    echo "Error: Please set YANDEX_OAUTH_TOKEN environment variable.\n";
    exit(1);
}

echo "========================================\n";
echo "Yandex Wordstat PHP SDK Real API Test\n";
echo "========================================\n\n";

$client = new WordstatClient($oauthToken);
$wordstat = new YandexWordstat($client);

// ==========================================
// Test 1: /userInfo
// ==========================================
echo "Test 1: Fetching User Info...\n";
try {
    $info = $wordstat->userInfo();
    echo "✅ User Info PASSED\n";
    echo "   User: {$info->userInfo->login}\n";
    echo "   Remaining quota: {$info->userInfo->dailyLimitRemaining} / {$info->userInfo->dailyLimit}\n\n";
} catch (Exception $e) {
    echo "❌ User Info FAILED: " . $e->getMessage() . "\n\n";
}

// ==========================================
// Test 2: /topRequests
// ==========================================
echo "Test 2: Fetching Top Requests for 'реставрировать фото онлайн'...\n";
try {
    $topRequests = $wordstat->topRequests(new TopRequestsRequest(
        phrase: 'реставрировать фото онлайн',
        devices: [DeviceType::PHONE]
    ));
    echo "✅ Top Requests PASSED\n";
    echo "   Total base count: {$topRequests->totalCount}\n";
    if (!empty($topRequests->topRequests)) {
        echo "   Top hit: '{$topRequests->topRequests[0]->phrase}' with count {$topRequests->topRequests[0]->count}\n\n";
    }
} catch (Exception $e) {
    echo "❌ Top Requests FAILED: " . $e->getMessage() . "\n\n";
}

// ==========================================
// Test 3: /dynamics
// ==========================================
echo "Test 3: Fetching Dynamics for 'яндекс'...\n";
try {
    $firstDayOfLastMonth = (new DateTime('first day of last month'))->format('Y-m-d');
    $lastDayOfLastMonth = (new DateTime('last day of last month'))->format('Y-m-d');

    $dynamics = $wordstat->dynamics(new DynamicsRequest(
        phrase: 'восстановить старые фото',
        period: DynamicsPeriod::MONTHLY,
        fromDate: $firstDayOfLastMonth,
        toDate: $lastDayOfLastMonth
    ));
    echo "✅ Dynamics PASSED\n";
    echo "   Fetched " . count($dynamics->dynamics) . " historical records.\n\n";
} catch (Exception $e) {
    echo "❌ Dynamics FAILED: " . $e->getMessage() . "\n\n";
}

// ==========================================
// Test 4: /regions
// ==========================================
echo "Test 4: Fetching Regional Interest for 'лыжи'...\n";
try {
    $regions = $wordstat->regions(new RegionsRequest(
        phrase: 'реставрировать фото ии',
        regionType: RegionType::CITIES
    ));
    echo "✅ Regions PASSED\n";
    echo "   Fetched data for " . count($regions->regions) . " regions.\n";
} catch (Exception $e) {
    echo "❌ Regions FAILED: " . $e->getMessage() . "\n";
}
