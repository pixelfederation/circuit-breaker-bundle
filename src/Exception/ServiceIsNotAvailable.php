<?php

/**
 * @author Juraj Surman <jsurman@pixelfederation.com>
 */

declare(strict_types=1);

namespace PixelFederation\CircuitBreakerBundle\Exception;

use DomainException;
use Throwable;

final class ServiceIsNotAvailable extends DomainException
{
    private const TEXT = 'Service %s is not available.';

    public static function createWithPrevious(string $serviceName, Throwable $previous): ServiceIsNotAvailable
    {
        return new self(
            sprintf(self::TEXT, $serviceName),
            (int) $previous->getCode(),
            $previous
        );
    }

    public static function create(string $serviceName): ServiceIsNotAvailable
    {
        return new self(
            sprintf(self::TEXT, $serviceName)
        );
    }
}
