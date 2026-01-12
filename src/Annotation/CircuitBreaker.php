<?php

declare(strict_types=1);

namespace PixelFederation\CircuitBreakerBundle\Annotation;

use Attribute;
use Throwable;

#[Attribute(Attribute::TARGET_METHOD)]
final class CircuitBreaker
{
    /**
     * @param array<int, class-string<Throwable>> $ignoreExceptions
     */
    public function __construct(
        private readonly ?string $fallbackMethod = null,
        private readonly array $ignoreExceptions = [],
    ) {
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
