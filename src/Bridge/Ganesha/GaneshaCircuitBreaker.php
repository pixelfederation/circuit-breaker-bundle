<?php

declare(strict_types=1);

namespace PixelFederation\CircuitBreakerBundle\Bridge\Ganesha;

use Ackintosh\Ganesha;
use Closure;
use Override;
use PixelFederation\CircuitBreakerBundle\CircuitBreaker;
use PixelFederation\CircuitBreakerBundle\CircuitBreakerConfiguration;
use PixelFederation\CircuitBreakerBundle\Exception\ServiceIsNotAvailable;
use Throwable;

// phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
final class GaneshaCircuitBreaker implements CircuitBreaker
{
    public function __construct(
        private readonly Ganesha $ganesha,
    ) {
    }

    #[Override]
    public function run(Closure $invoker, Closure $fallback, CircuitBreakerConfiguration $configuration): mixed
    {
        if (!$this->ganesha->isAvailable($configuration->getServiceName())) {
            return $fallback(ServiceIsNotAvailable::create($configuration->getServiceName()));
        }
        try {
            /** @psalm-suppress MixedAssignment */
            $result = $invoker();
            $this->ganesha->success($configuration->getServiceName());

            return $result;
        } catch (Throwable $e) {
            if ($this->shouldFail($e, $configuration)) {
                $this->ganesha->failure($configuration->getServiceName());
            }

            return $fallback(ServiceIsNotAvailable::createWithPrevious($configuration->getServiceName(), $e));
        }
    }

    private function shouldFail(Throwable $exception, CircuitBreakerConfiguration $configuration): bool
    {
        foreach ($configuration->getIgnoreExceptions() as $ignoreException) {
            if (is_a($exception, $ignoreException)) {
                return false;
            }
        }

        return true;
    }
}
