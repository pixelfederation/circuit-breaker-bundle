<?php

/**
 * @author Juraj Surman <jsurman@pixelfederation.com>
 */

declare(strict_types=1);

namespace PixelFederation\CircuitBreakerBundle;

use PixelFederation\CircuitBreakerBundle\Annotation\CircuitBreakerService;
use Throwable;

final class CircuitBreakerConfiguration
{
    private string $serviceName;

    private ?string $defaultFallback;

    /**
     * @var array<int, class-string<Throwable>>
     */
    private array $ignoreExceptions;

    /**
     * @param array<int, class-string<Throwable>> $ignoreExceptions
     */
    private function __construct(string $serviceName, ?string $defaultFallback, array $ignoreExceptions)
    {
        $this->serviceName = $serviceName;
        $this->defaultFallback = $defaultFallback;
        $this->ignoreExceptions = $ignoreExceptions;
    }

    public static function fromAnnotation(
        string $serviceClass,
        CircuitBreakerService $annotation
    ): CircuitBreakerConfiguration {
        $serviceName = $annotation->getServiceName() ?? $serviceClass;

        return new self($serviceName, $annotation->getDefaultFallback(), $annotation->getIgnoreExceptions());
    }

    public static function fromServiceName(string $serviceName): CircuitBreakerConfiguration
    {
        return new self($serviceName, null, []);
    }

    public function getServiceName(): string
    {
        return $this->serviceName;
    }

    public function getDefaultFallback(): ?string
    {
        return $this->defaultFallback;
    }

    /**
     * @param array<int, class-string<Throwable>> $ignoreExceptions
     */
    public function withIgnoreExceptions(array $ignoreExceptions): CircuitBreakerConfiguration
    {
        return new self(
            $this->serviceName,
            $this->defaultFallback,
            array_merge($this->ignoreExceptions, $ignoreExceptions)
        );
    }

    /**
     * @return array<int, class-string<Throwable>>
     */
    public function getIgnoreExceptions(): array
    {
        return $this->ignoreExceptions;
    }
}
