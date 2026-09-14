<?php

declare(strict_types=1);

namespace PixelFederation\CircuitBreakerBundle\Tests\Unit\Bridge\Symfony\Command;

use PHPUnit\Framework\TestCase;
use PixelFederation\CircuitBreakerBundle\Bridge\Symfony\Command\GenerateCircuitBrokenProxiesCacheWarmer;
use PixelFederation\CircuitBreakerBundle\Generator;
use PixelFederation\CircuitBreakerBundle\MethodExtractor;
use PixelFederation\CircuitBreakerBundle\ServicesMethods;
use ProxyManager\Configuration;
use Symfony\Component\Filesystem\Filesystem;

final class GenerateCircuitBrokenProxiesCacheWarmerTest extends TestCase
{
    private Filesystem $filesystem;

    private string $baseDir;

    private string $cacheDirectory;

    /**
     * Regression: the warmer must create the cache directory before running the generator.
     *
     * ProxyManager's FileLocator calls realpath() in its constructor and throws
     * InvalidProxyDirectoryException when the directory is missing (e.g. right after the
     * cache clearer removed it). Guarantee the directory exists so the generator never
     * constructs a FileLocator against a missing path.
     */
    public function testWarmUpCreatesTheProxyDirectoryWhenItIsMissing(): void
    {
        self::assertDirectoryDoesNotExist($this->cacheDirectory);

        $warmer = new GenerateCircuitBrokenProxiesCacheWarmer(
            $this->createGeneratorWithoutServices(),
            $this->filesystem,
            $this->cacheDirectory,
        );

        $warmer->warmUp($this->baseDir);

        self::assertDirectoryExists($this->cacheDirectory);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->filesystem = new Filesystem();
        $this->baseDir = sys_get_temp_dir() . '/' . uniqid('pixelfederation_cicruit_breaker_', true);
        $this->cacheDirectory = $this->baseDir . '/cache';
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->filesystem->remove($this->baseDir);
    }

    /**
     * A Generator with no circuit-broken services generates nothing and, in particular, never
     * creates the proxy directory itself - so the directory can only exist if the warmer made it.
     */
    private function createGeneratorWithoutServices(): Generator
    {
        $methodExtractor = $this->createStub(MethodExtractor::class);
        $methodExtractor->method('extractAll')
            ->willReturn(new ServicesMethods());

        return new Generator(new Configuration(), $methodExtractor);
    }
}
