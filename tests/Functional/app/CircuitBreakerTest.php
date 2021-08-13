<?php

/**
 * @author Martin Fris <mfris@pixelfederation.com>
 */

declare(strict_types=1);

namespace PixelFederation\CircuitBreakerBundle\Tests\Tests\Functional\app;

use PixelFederation\CircuitBreakerBundle\Tests\Functional\app\CircuitBreaker\Service\GaneshaSpy;
use PixelFederation\CircuitBreakerBundle\Tests\Functional\app\CircuitBreaker\Service\TestService;
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

    /**
     * @dataProvider dataProvider
     */
    public function testCircuitBrokenService(
        string $method,
        ?string $argument,
        array $expectedCalls,
        int $expectedFailures
    ): void {

        /** @var TestService $service */
        $service = self::getContainer()->get(TestService::class);

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

    public function dataProvider(): array
    {
        return [
            [
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
                'runWithDefaultFalback',
                null,
                ['runWithDefaultFalback', 'defaultFallback'],
                1,
            ],
            [
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
