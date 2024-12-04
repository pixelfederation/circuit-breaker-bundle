<?php

declare(strict_types=1);

namespace PixelFederation\CircuitBreakerBundle\Tests\Functional\app;

use PHPUnit\Framework\Attributes\DataProvider;
use PixelFederation\CircuitBreakerBundle\Tests\Functional\app\CircuitBreaker\Service\GaneshaSpy;
use PixelFederation\CircuitBreakerBundle\Tests\Functional\app\CircuitBreaker\Service\TestServiceWithAnnotations;
use PixelFederation\CircuitBreakerBundle\Tests\Functional\app\CircuitBreaker\Service\TestServiceWithAttributes;
use PixelFederation\CircuitBreakerBundle\Tests\Functional\TestCase;

final class CircuitBreakerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        apcu_clear_cache();
        self::bootTestKernel();
        self::runCommand('cache:clear --no-warmup');
        self::runCommand('cache:warmup --no-debug');
    }

    #[DataProvider('dataProvider')]
    public function testCircuitBrokenService(
        string $serviceClass,
        string $method,
        ?string $argument,
        array $expectedCalls,
        int $expectedFailures
    ): void {
        /** @var TestServiceWithAnnotations|TestServiceWithAttributes $service */
        $service = self::getContainer()->get($serviceClass);

        if ($argument !== null) {
            $service->{$method}($argument);
        } else {
            $service->{$method}();
        }

        self::assertSame($expectedCalls, $service->getCalledMethodsWithArgs());

        /** @var GaneshaSpy $store */
        $ganesha = self::getContainer()->get('pixel_federation_circuit_breaker.ganesha');

        self::assertSame($ganesha->getFailureCallCount(), $expectedFailures);
    }

    public static function dataProvider(): array
    {
        return [
            [
                TestServiceWithAnnotations::class,
                'runWithStackedFallbacks',
                null,
                [
                    'runWithStackedFallbacks:something',
                    'fallback:something',
                    'fallbackForFallback:something',
                    'lastFallback:something'
                ],
                3,
            ],
            [
                TestServiceWithAnnotations::class,
                'runWithDefaultFalback',
                null,
                ['runWithDefaultFalback', 'defaultFallback'],
                1,
            ],
            [
                TestServiceWithAnnotations::class,
                'runWithIgnoredFallback',
                'abc',
                ['runWithIgnoredFallback:abc', 'fallback:abc', 'fallbackForFallback:abc', 'lastFallback:abc'],
                2,
            ],
            [
                TestServiceWithAttributes::class,
                'runWithStackedFallbacks',
                null,
                [
                    'runWithStackedFallbacks:something',
                    'fallback:something',
                    'fallbackForFallback:something',
                    'lastFallback:something'
                ],
                3,
            ],
            [
                TestServiceWithAttributes::class,
                'runWithDefaultFalback',
                null,
                ['runWithDefaultFalback', 'defaultFallback'],
                1,
            ],
            [
                TestServiceWithAttributes::class,
                'runWithIgnoredFallback',
                'abc',
                ['runWithIgnoredFallback:abc', 'fallback:abc', 'fallbackForFallback:abc', 'lastFallback:abc'],
                2,
            ],
        ];
    }

    protected static function getTestCase(): string
    {
        return 'CircuitBreaker';
    }
}
