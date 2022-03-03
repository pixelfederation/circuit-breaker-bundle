<?php

/**
 * @author Juraj Surman <jsurman@pixelfederation.com>
 */

declare(strict_types=1);

namespace PixelFederation\CircuitBreakerBundle;

use Throwable;
use UnexpectedValueException;

final class ServiceMethod
{
    private string $name;

    private ?string $fallback;

    /**
     * @var array<int, class-string<Throwable>>
     */
    private array $ignoredExceptions;

    /**
     * @param array<int, class-string<Throwable>> $ignoredExceptions
     */
    public function __construct(string $name, ?string $fallback, array $ignoredExceptions)
    {
        $this->name = $name;
        $this->fallback = $fallback;
        $this->ignoredExceptions = $ignoredExceptions;
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
