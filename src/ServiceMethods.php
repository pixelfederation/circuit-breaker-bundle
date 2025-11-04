<?php

declare(strict_types=1);

namespace PixelFederation\CircuitBreakerBundle;

use ArrayIterator;
use IteratorAggregate;
use Override;
use Traversable;

/**
 * @template-implements IteratorAggregate<array-key, ServiceMethod>
 */
final class ServiceMethods implements IteratorAggregate
{
    /**
     * @var array<array-key, ServiceMethod>
     */
    private readonly array $methods;

    /**
     * @param class-string<CircuitBrokenService> $class
     */
    public function __construct(
        private readonly string $class,
        private readonly CircuitBreakerConfiguration $configuration,
        ServiceMethod ...$methods,
    ) {
        $this->methods = $methods;
    }

    public function findByMethodName(string $methodName): ?ServiceMethod
    {
        foreach ($this->methods as $method) {
            if ($method->getName() === $methodName) {
                return $method;
            }
        }

        return null;
    }

    /**
     * @return class-string<CircuitBrokenService>
     */
    public function getClass(): string
    {
        return $this->class;
    }

    public function getConfiguration(): CircuitBreakerConfiguration
    {
        return $this->configuration;
    }

    /**
     * @return ArrayIterator<array-key, ServiceMethod>
     */
    #[Override]
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->methods);
    }
}
