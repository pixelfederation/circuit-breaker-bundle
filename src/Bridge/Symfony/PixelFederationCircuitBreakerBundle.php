<?php

declare(strict_types=1);

namespace PixelFederation\CircuitBreakerBundle\Bridge\Symfony;

use Override;
use PixelFederation\CircuitBreakerBundle\Bridge\Symfony\DependencyInjection\CompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @psalm-suppress DeprecatedInterface
 */
final class PixelFederationCircuitBreakerBundle extends Bundle
{
    #[Override]
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new CompilerPass\CircuitBrokenServiceProxyGeneratorPass());
    }
}
