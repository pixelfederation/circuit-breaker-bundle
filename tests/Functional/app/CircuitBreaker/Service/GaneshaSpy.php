<?php

/**
 * @author Martin Fris <mfris@pixelfederation.com>
 */

declare(strict_types=1);

namespace PixelFederation\CircuitBreakerBundle\Tests\Functional\app\CircuitBreaker\Service;

use Ackintosh\Ganesha;

class GaneshaSpy extends Ganesha
{
    private Ganesha $decorated;

    private int $failureCallCount = 0;

    public function __construct(Ganesha $decorated)
    {
        $this->decorated = $decorated;
    }

    public function failure($service): void
    {
        $this->failureCallCount++;
        $this->decorated->failure($service);
    }

    public function success($service): void
    {
        $this->decorated->success($service);
    }

    public function isAvailable($service): bool
    {
        return $this->decorated->isAvailable($service);
    }

    public function subscribe(callable $callable): void
    {
        $this->decorated->subscribe($callable);
    }

    public static function disable(): void
    {
        parent::disable();
    }

    public static function enable(): void
    {
        parent::enable();
    }

    public function reset(): void
    {
        $this->decorated->reset();
    }

    public function getFailureCallCount(): int
    {
        return $this->failureCallCount;
    }
}
