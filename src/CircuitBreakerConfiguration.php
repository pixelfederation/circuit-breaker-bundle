<?php

declare(strict_types=1);

namespace PixelFederation\CircuitBreakerBundle;

use PixelFederation\CircuitBreakerBundle\Annotation\CircuitBreakerService;
use Throwable;

final class CircuitBreakerConfiguration
{
    /**
     * @param array<int, class-string<Throwable>> $ignoreExceptions
     */
    private function __construct(
        private readonly string $serviceName,
        private readonly ?string $defaultFallback,
        private readonly array $ignoreExceptions,
    ) {
    }

    public static function fromAnnotation(
        string $serviceClass,
        CircuitBreakerService $annotation,
    ): self {
        $serviceName = $annotation->getServiceName() ?? $serviceClass;

        return new self($serviceName, $annotation->getDefaultFallback(), $annotation->getIgnoreExceptions());
    }

    public static function fromServiceName(string $serviceName): self
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
    public function withIgnoreExceptions(array $ignoreExceptions): self
    {
        return new self(
            $this->serviceName,
            $this->defaultFallback,
            array_merge($this->ignoreExceptions, $ignoreExceptions),
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
