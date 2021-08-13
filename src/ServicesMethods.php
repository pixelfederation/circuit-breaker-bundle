<?php

/**
 * @author Martin Fris <mfris@pixelfederation.com>
 */

declare(strict_types=1);

namespace PixelFederation\CircuitBreakerBundle;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

/**
 * @template-implements IteratorAggregate<class-string<CircuitBrokenService>, ServiceMethods>
 */
final class ServicesMethods implements IteratorAggregate
{
    /**
     * @var array<class-string<CircuitBrokenService>, ServiceMethods>
     */
    private array $methods = [];

    public function add(ServiceMethods $serviceMethods): void
    {
        $this->methods[$serviceMethods->getClass()] = $serviceMethods;
    }

    /**
     * @param class-string<CircuitBrokenService> $serviceClass
     */
    public function getForService(string $serviceClass): ServiceMethods
    {
        return $this->methods[$serviceClass];
    }

    /**
     * @return array<class-string<CircuitBrokenService>>
     */
    public function getClassNames(): array
    {
        return array_keys($this->methods); // phpcs:ignore
    }

    /**
     * @return Traversable<class-string<CircuitBrokenService>, ServiceMethods>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->methods);
    }
}
