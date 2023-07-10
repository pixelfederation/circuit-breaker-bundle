<?php

declare(strict_types=1);

namespace PixelFederation\CircuitBreakerBundle\Annotation;

use Attribute;
use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Throwable;

/**
 * @Annotation
 * @Annotation\Target("CLASS")
 * @NamedArgumentConstructor
 * @Annotation\Attributes({
 * @Annotation\Attribute(
 *     "serviceName",
 *     type="string",
 *     required=false
 *   ),
 * @Annotation\Attribute(
 *     "defaultFallback",
 *     type="string",
 *     required=false
 *   ),
 * @Annotation\Attribute(
 *     "ignoreExceptions",
 *     type="array",
 *     required=false
 *   )
 * })
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class CircuitBreakerService
{
    /**
     * @param array<int, class-string<Throwable>> $ignoreExceptions
     */
    public function __construct(
        private readonly ?string $serviceName = null,
        private readonly ?string $defaultFallback = null,
        private readonly array $ignoreExceptions = [],
    ) {
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
