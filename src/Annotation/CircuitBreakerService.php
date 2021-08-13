<?php

/**
 * @author Juraj Surman <jsurman@pixelfederation.com>
 */

declare(strict_types=1);

namespace PixelFederation\CircuitBreakerBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Throwable;

/**
 * @Annotation
 * @Annotation\Target("CLASS")
 */
final class CircuitBreakerService
{
    private ?string $serviceName;

    private ?string $defaultFallback;

    /**
     * @var array<int, class-string<Throwable>>
     */
    private array $ignoreExceptions;

    /**
     * @param array{
     *     value?: string,
     *     serviceName?: string,
     *     defaultFallback?: string,
     *     ignoreExceptions?: array<int, class-string<Throwable>>
     * } $values
     */
    public function __construct(array $values)
    {
        $this->serviceName = $values['value'] ?? $values['serviceName'] ?? null;
        $this->defaultFallback = $values['defaultFallback'] ?? null;
        $this->ignoreExceptions = $values['ignoreExceptions'] ?? [];
    }

    public function getServiceName(): ?string
    {
        return $this->serviceName;
    }

    public function getDefaultFallback(): ?string
    {
        return $this->defaultFallback;
    }

    /**
     * @return array<int, class-string<Throwable>>
     */
    public function getIgnoreExceptions(): array
    {
        return $this->ignoreExceptions;
    }
}
