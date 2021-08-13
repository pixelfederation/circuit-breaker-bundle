<?php

/**
 * @author Martin Fris <mfris@pixelfederation.com>
 */

declare(strict_types=1);

namespace PixelFederation\CircuitBreakerBundle\Bridge\Symfony;

use PixelFederation\CircuitBreakerBundle\Bridge\Symfony\DependencyInjection\{
    CompilerPass\CircuitBrokenServiceProxyGeneratorPass,
};
use Symfony\Component\DependencyInjection\ContainerBuilder; // phpcs:ignore
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class PixelFederationCircuitBreakerBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new CircuitBrokenServiceProxyGeneratorPass());
    }
}
