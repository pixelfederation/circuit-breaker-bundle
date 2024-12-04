<?php

declare(strict_types=1);

namespace PixelFederation\CircuitBreakerBundle\Bridge\Symfony\Command;

use Composer\InstalledVersions;
use PixelFederation\CircuitBreakerBundle\Generator;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

// phpcs:disable
// This is a workaround for the Symfony 6.4 compatibility
// after dropping support for Symfony 6.4 KEEP ONLY THE "IF" PART !!!
if (version_compare(InstalledVersions::getVersion('symfony/http-kernel'), '7.0', '>=')) {
// phpcs:enable
    final class GenerateCircuitBrokenProxiesCacheWarmer implements CacheWarmerInterface
    {
        public function __construct(
            private readonly Generator $proxyGenerator,
        ) {
        }

        public function isOptional(): bool
        {
            return true;
        }

        /**
         * @return array<string>
         * @SuppressWarnings(PHPMD.UnusedFormalParameter)
         */
        public function warmUp(string $cacheDir, ?string $buildDir = null): array // phpcs:ignore
        {
            $this->proxyGenerator->generate();

            return [];
        }
    }
// phpcs:disable
} else {
    final class GenerateCircuitBrokenProxiesCacheWarmer implements CacheWarmerInterface
    {
        public function __construct(
            private readonly Generator $proxyGenerator,
        ) {
        }

        public function isOptional(): bool
        {
            return true;
        }

        /**
         * @return array<string>
         * @SuppressWarnings(PHPMD.UnusedFormalParameter)
         */
        public function warmUp(string $cacheDir): array // phpcs:ignore
        {
            $this->proxyGenerator->generate();

            return [];
        }
    }
}
