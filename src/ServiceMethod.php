<?php

declare(strict_types=1);

namespace PixelFederation\CircuitBreakerBundle;

use Throwable;
use UnexpectedValueException;

final class ServiceMethod
{
    /**
     * @param array<int, class-string<Throwable>> $ignoredExceptions
     */
    public function __construct(
        private readonly string $name,
        private readonly ?string $fallback,
        private readonly array $ignoredExceptions,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function hasFallback(): bool
    {
        return $this->fallback !== null;
    }

    /**
     * @psalm-suppress InvalidNullableReturnType
     */
    public function getFallback(): string
    {
        if ($this->fallback === null) {
            throw new UnexpectedValueException('Fallback was not specified.');
        }

        return $this->fallback;
    }

    /**
     * @return array<int, class-string<Throwable>>
     */
    public function getIgnoredExceptions(): array
    {
        return $this->ignoredExceptions;
    }
}
