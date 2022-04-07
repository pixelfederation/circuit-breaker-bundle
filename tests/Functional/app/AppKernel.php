<?php

/**
 * @author Martin Fris <mfris@pixelfederation.com>
 */

declare(strict_types=1);

namespace PixelFederation\CircuitBreakerBundle\Tests\Functional\app;

use Exception;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel;

final class AppKernel extends Kernel
{
    private string $varDir;

    private string $testCase;

    private string $rootConfig;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(string $varDir, string $testCase, string $rootConfig, string $environment, bool $debug)
    {
        if (!is_dir(__DIR__ . '/' . $testCase)) {
            throw new InvalidArgumentException(sprintf('The test case "%s" does not exist.', $testCase));
        }

        $this->varDir = $varDir;
        $this->testCase = $testCase;
        $filesystem = new Filesystem();

        if (!$filesystem->isAbsolutePath($rootConfig)
            && !is_file($rootConfig = __DIR__ . '/' . $testCase . '/' . $rootConfig)
        ) {
            throw new InvalidArgumentException(sprintf('The root config "%s" does not exist.', $rootConfig));
        }

        $this->rootConfig = $rootConfig;

        parent::__construct($environment, $debug);
    }

    /**
     * @return mixed|\Symfony\Component\HttpKernel\Bundle\BundleInterface[]
     * @throws RuntimeException
     */
    public function registerBundles(): iterable
    {
        if (!is_file($filename = $this->getProjectDir() . '/config/bundles.php')) {
            throw new RuntimeException(sprintf('The bundles file "%s" does not exist.', $filename));
        }

        return include $filename;
    }

    public function getProjectDir(): string
    {
        return __DIR__;
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir() . '/' . $this->varDir . '/' . $this->testCase . '/cache/' . $this->environment;
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir() . '/' . $this->varDir . '/' . $this->testCase . '/logs';
    }

    /**
     * @throws Exception
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->rootConfig);
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return serialize([
            $this->varDir,
            $this->testCase,
            $this->rootConfig,
            $this->getEnvironment(),
            $this->isDebug()
        ]);
    }

    /**
     * @param $str
     * @throws InvalidArgumentException
     */
    public function unserialize($str)
    {
        $data = unserialize($str);
        $this->__construct($data[0], $data[1], $data[2], $data[3], $data[4]);
    }

    protected function getKernelParameters(): array
    {
        $parameters = parent::getKernelParameters();
        $parameters['kernel.test_case'] = $this->testCase;

        return $parameters;
    }
}
