<?php

/**
 * @author Martin Fris <mfris@pixelfederation.com>
 * @author Juraj Surman <jsurman@pixelfederation.com>
 */

declare(strict_types=1);

namespace PixelFederation\CircuitBreakerBundle;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

/**
 * @template-implements IteratorAggregate<array-key, ServiceMethod>
 */
final class ServiceMethods implements IteratorAggregate
{
    /**
     * @var class-string<CircuitBrokenService>
     */
    private string $class;

    private CircuitBreakerConfiguration $configuration;

    /**
     * @var array<array-key, ServiceMethod>
     */
    private array $methods;

    /**
     * @param class-string<CircuitBrokenService> $class
     */
    public function __construct(string $class, CircuitBreakerConfiguration $configuration, ServiceMethod ...$methods)
    {
        $this->class = $class;
        $this->configuration = $configuration;
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
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->methods);
    }
}
