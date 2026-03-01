# Yandex Wordstat PHP SDK

Строго типизированный, современный PHP SDK для нового REST API Яндекс Wordstat. Построен на `amphp/http-client` для высокой производительности и `crell/serde` для надёжной сериализации объектов.

## Возможности

- **Полное покрытие API**: поддерживает эндпоинты `/getRegionsTree`, `/topRequests`, `/dynamics`, `/regions` и `/userInfo`.
- **Строгая типизация**: использует типизированные DTO-запросы и объекты ответов.
- **Гибкая фильтрация**: фильтрация по регионам, устройствам и временным периодам через удобные enum-классы.
- **Пакетные запросы**: поддержка нескольких фраз в одном запросе к `/topRequests`.
- **Пользовательские исключения**: чистая обработка ошибок с маппингом на структуры ошибок Yandex API, включая автоматическое определение Rate Limit.

## Документация

📚 **[Смотреть полную документацию в Wiki](../../wiki)**

### Быстрые ссылки

- [Начало работы и обзор REST API](../../wiki/REST-API)
- [/topRequests — Популярные запросы](../../wiki/top-requests)
- [/dynamics — Динамика запросов](../../wiki/dynamics)
- [/regions — Региональная статистика](../../wiki/regions)
- [/userInfo — Информация о пользователе](../../wiki/user-info)
- [/getRegionsTree — Дерево регионов](../../wiki/get-regions-tree)

## Установка

```bash
composer require shanginn/yandex-wordstat
```

## Начало работы

### Инициализация

```php
use Shanginn\YandexWordstat\WordstatClient;
use Shanginn\YandexWordstat\YandexWordstat;

$oauthToken = getenv('YANDEX_OAUTH_TOKEN');

$client = new WordstatClient($oauthToken);
$wordstat = new YandexWordstat($client);
```

### 1. Информация о пользователе и квоте

```php
$userInfo = $wordstat->userInfo();

echo "Логин: {$userInfo->userInfo->login}\n";
echo "Осталось запросов: {$userInfo->userInfo->dailyLimitRemaining} / {$userInfo->userInfo->dailyLimit}\n";
```

### 2. Популярные запросы

```php
use Shanginn\YandexWordstat\Requests\TopRequestsRequest;
use Shanginn\YandexWordstat\Enums\DeviceType;

$result = $wordstat->topRequests(new TopRequestsRequest(
    phrase: 'яндекс',
    regions: [213, 2], // Москва и Санкт-Петербург
    devices: [DeviceType::PHONE]
));

echo "Всего запросов: {$result->totalCount}\n";
foreach ($result->topRequests as $req) {
    echo "{$req->phrase} — {$req->count} показов\n";
}
```

### 3. Пакетный запрос для нескольких фраз

```php
$results = $wordstat->topRequests(new TopRequestsRequest(
    phrase: ['яндекс', 'гугл', 'mail']
));

// При передаче массива фраз возвращается массив TopRequestsResult
foreach ($results as $result) {
    echo "Фраза: {$result->requestPhrase}, показов: {$result->totalCount}\n";
}
```

### 4. Динамика запросов

```php
use Shanginn\YandexWordstat\Requests\DynamicsRequest;
use Shanginn\YandexWordstat\Enums\DynamicsPeriod;

$dynamics = $wordstat->dynamics(new DynamicsRequest(
    phrase: 'яндекс',
    period: DynamicsPeriod::MONTHLY,
    fromDate: '2025-01-01',
    toDate: '2025-03-31'
));

foreach ($dynamics->dynamics as $item) {
    echo "Дата: {$item->date} | Показы: {$item->count} | Доля: {$item->share}%\n";
}
```

### 5. Региональная статистика

```php
use Shanginn\YandexWordstat\Requests\RegionsRequest;
use Shanginn\YandexWordstat\Enums\RegionType;

$regionsInfo = $wordstat->regions(new RegionsRequest(
    phrase: 'яндекс',
    regionType: RegionType::CITIES
));

foreach ($regionsInfo->regions as $region) {
    echo "Регион ID: {$region->regionId} | Показы: {$region->count} | Индекс интереса: {$region->affinityIndex}\n";
}
```

### 6. Дерево регионов

```php
// Возвращает иерархическое дерево регионов Яндекса
$tree = $wordstat->getRegionsTree();

foreach ($tree as $country) {
    echo "Страна: {$country['name']} (ID: {$country['id']})\n";
    foreach ($country['children'] as $region) {
        echo "  Регион: {$region['name']} (ID: {$region['id']})\n";
    }
}
```

## Аутентификация

Для работы с SDK необходим действующий OAuth-токен Яндекса с доступом к Wordstat API.

Установите переменную окружения:

```bash
export YANDEX_OAUTH_TOKEN=your_oauth_token_here
```

## Доступные enum-классы

### DeviceType — тип устройства

| Значение | Описание |
|----------|----------|
| `DeviceType::ALL` | Все устройства |
| `DeviceType::DESKTOP` | Компьютеры |
| `DeviceType::MOBILE` | Мобильные устройства |
| `DeviceType::PHONE` | Телефоны |
| `DeviceType::TABLET` | Планшеты |

### DynamicsPeriod — период динамики

| Значение | Описание |
|----------|----------|
| `DynamicsPeriod::DAILY` | По дням |
| `DynamicsPeriod::WEEKLY` | По неделям |
| `DynamicsPeriod::MONTHLY` | По месяцам |

### RegionType — тип региона

| Значение | Описание |
|----------|----------|
| `RegionType::ANY` | Любые регионы |
| `RegionType::CITIES` | Города |
| `RegionType::REGIONS` | Регионы |
| `RegionType::EVERYWHERE` | Везде |

## Обработка ошибок

```php
use Shanginn\YandexWordstat\Exceptions\WordstatRateLimitException;
use Shanginn\YandexWordstat\Exceptions\WordstatApiErrorException;
use Shanginn\YandexWordstat\Exceptions\WordstatException;

try {
    $result = $wordstat->topRequests(new TopRequestsRequest(phrase: 'яндекс'));
} catch (WordstatRateLimitException $e) {
    // Превышен лимит запросов (HTTP 429)
    echo "Лимит запросов превышен. Повторите позже.\n";
} catch (WordstatApiErrorException $e) {
    // Ошибка API (HTTP 4xx/5xx)
    echo "Ошибка API [{$e->getStatusCode()}]: {$e->getMessage()}\n";
} catch (WordstatException $e) {
    // Общая ошибка SDK
    echo "Ошибка: {$e->getMessage()}\n";
}
```

## Запуск тестов

```bash
composer install
./vendor/bin/phpunit
```

## Лицензия

MIT
