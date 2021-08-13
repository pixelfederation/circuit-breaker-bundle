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
 * @Annotation\Target("METHOD")
 */
final class CircuitBreaker
{
    private ?string $fallbackMethod;

    /**
     * @var array<int, class-string<Throwable>>
     */
    private array $ignoreExceptions;

    /**
     * @param array{
     *     value?: string,
     *     fallbackMethod?: string,
     *     ignoreExceptions?: array<int, class-string<Throwable>>
     * } $values
     */
    public function __construct(array $values)
    {
        $this->fallbackMethod = $values['value'] ?? $values['fallbackMethod'] ?? null;
        $this->ignoreExceptions = $values['ignoreExceptions'] ?? [];
    }

    public function getFallbackMethod(): ?string
    {
        return $this->fallbackMethod;
    }

    /**
     * @return array<int, class-string<Throwable>>
     */
    public function getIgnoreExceptions(): array
    {
        return $this->ignoreExceptions;
    }
}
