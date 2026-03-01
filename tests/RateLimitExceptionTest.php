<?php

declare(strict_types=1);

namespace Tests;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Shanginn\YandexWordstat\YandexWordstat;
use Shanginn\YandexWordstat\WordstatClientInterface;
use Shanginn\YandexWordstat\Requests\TopRequestsRequest;
use Shanginn\YandexWordstat\Exceptions\WordstatRateLimitException;
use Shanginn\YandexWordstat\Exceptions\WordstatApiErrorException;
use Shanginn\YandexWordstat\Exceptions\WordstatException;

class RateLimitExceptionTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private $mockClient;
    private YandexWordstat $wordstat;

    protected function setUp(): void
    {
        $this->mockClient = Mockery::mock(WordstatClientInterface::class);
        $this->wordstat = new YandexWordstat($this->mockClient);
    }

    public function testRateLimitExceptionIsThrown(): void
    {
        $this->mockClient
            ->shouldReceive('post')
            ->once()
            ->andThrow(new WordstatRateLimitException('Rate limit exceeded'));

        $this->expectException(WordstatRateLimitException::class);
        $this->expectExceptionMessage('Rate limit exceeded');
        $this->expectExceptionCode(429);

        $this->wordstat->topRequests(new TopRequestsRequest(phrase: 'яндекс'));
    }

    public function testRateLimitExceptionCode(): void
    {
        $exception = new WordstatRateLimitException('Too many requests');

        $this->assertEquals(429, $exception->getCode());
        $this->assertEquals(429, $exception->getStatusCode());
    }

    public function testRateLimitExceptionDefaultMessage(): void
    {
        $exception = new WordstatRateLimitException();

        $this->assertStringContainsString('Rate limit exceeded', $exception->getMessage());
        $this->assertEquals(429, $exception->getStatusCode());
    }

    public function testRateLimitExceptionExtendsApiError(): void
    {
        $exception = new WordstatRateLimitException('Too many requests');

        $this->assertInstanceOf(WordstatApiErrorException::class, $exception);
        $this->assertInstanceOf(WordstatException::class, $exception);
    }

    public function testApiErrorExceptionStatusCode(): void
    {
        $exception = new WordstatApiErrorException('Not found', 404);

        $this->assertEquals(404, $exception->getStatusCode());
        $this->assertStringContainsString('Not found', $exception->getMessage());
    }
}
