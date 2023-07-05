<?php

declare(strict_types=1);

namespace PixelFederation\CircuitBreakerBundle\Tests\Functional\app\CircuitBreaker\Service;

use PixelFederation\CircuitBreakerBundle\Annotation\CircuitBreaker;
use PixelFederation\CircuitBreakerBundle\Annotation\CircuitBreakerService;
use PixelFederation\CircuitBreakerBundle\CircuitBrokenService;
use BadMethodCallException;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use RuntimeException;

/**
 * @CircuitBreakerService(defaultFallback="defaultFallback", ignoreExceptions={BadMethodCallException::class})
 */
class TestService implements CircuitBrokenService
{
    /**
     * @var array<string>
     */
    private array $calledMethodsWithArgs = [];

    public function __construct(
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @CircuitBreaker()
     */
    public function runWithDefaultFalback(): void
    {
        $this->logger->debug(__FUNCTION__);
        $this->calledMethodsWithArgs[] = __FUNCTION__;
        throw new InvalidArgumentException(__FUNCTION__);
    }

    /**
     * @CircuitBreaker(fallbackMethod="fallback")
     */
    public function runWithStackedFallbacks(string $withSomething = 'something'): void
    {
        $this->logger->debug(__FUNCTION__);
        $this->calledMethodsWithArgs[] = __FUNCTION__ . ':' . $withSomething;
        throw new InvalidArgumentException(__FUNCTION__);
    }

    /**
     * @CircuitBreaker(fallbackMethod="fallback", ignoreExceptions={\RuntimeException::class})
     */
    public function runWithIgnoredFallback(string $withSomething = 'something'): void
    {
        $this->logger->debug(__FUNCTION__);
        $this->calledMethodsWithArgs[] = __FUNCTION__ . ':' . $withSomething;
        throw new RuntimeException(__FUNCTION__);
    }

    /**
     * @CircuitBreaker(fallbackMethod="fallbackForFallback")
     */
    public function fallback(string $what): void
    {
        $this->logger->debug(__FUNCTION__);
        $this->calledMethodsWithArgs[] = __FUNCTION__ . ':' . $what;
        throw new InvalidArgumentException(__FUNCTION__);
    }

    /**
     * @CircuitBreaker(fallbackMethod="lastFallback")
     */
    public function fallbackForFallback(string $bla): void
    {
        $this->logger->debug(__FUNCTION__);
        $this->calledMethodsWithArgs[] = __FUNCTION__ . ':' . $bla;
        throw new InvalidArgumentException(__FUNCTION__);
    }

    /**
     * @CircuitBreaker(fallbackMethod="lastMaybeNotLeastFallback")
     */
    public function lastFallback(string $last): void
    {
        $this->logger->debug(__FUNCTION__);
        $this->calledMethodsWithArgs[] = __FUNCTION__ . ':' . $last;
    }

    public function lastMaybeNotLeastFallback(string $never): void
    {
        $this->logger->debug(__FUNCTION__);
        $this->calledMethodsWithArgs[] = __FUNCTION__ . ':' . $never;
    }

    public function defaultFallback(): void
    {
        $this->logger->debug(__FUNCTION__);
        $this->calledMethodsWithArgs[] = __FUNCTION__;
    }

    public function getCalledMethodsWithArgs(): array
    {
        return $this->calledMethodsWithArgs;
    }
}
