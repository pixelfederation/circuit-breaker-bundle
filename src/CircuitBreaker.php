<?php

declare(strict_types=1);

namespace PixelFederation\CircuitBreakerBundle;

use Closure;

// phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
interface CircuitBreaker
{
    public function run(Closure $invoker, Closure $fallback, CircuitBreakerConfiguration $configuration): mixed;
}
