<?php

declare(strict_types=1);

namespace PixelFederation\CircuitBreakerBundle;

use ProxyManager\Configuration;
use ProxyManager\Factory\AccessInterceptorValueHolderFactory;
use ProxyManager\Version;

/**
 * @property Configuration $configuration
 */
final class Generator extends AccessInterceptorValueHolderFactory
{
    /**
     * Cached checked class names
     *
     * @var array<class-string>
     */
    private array $checkedClasses = [];

    public function __construct(
        Configuration $configuration,
        private readonly MethodExtractor $methodExtractor,
    ) {
        parent::__construct($configuration);
    }

    public function generate(): void
    {
        $repositoriesMethods = $this->generateClassMethodsList();
        $this->generateProxies($repositoriesMethods);
    }

    /**
     * this override method activates the proxy manage class autoloader, which is kind of pain in the ass
     * to activate in Symfony, since Symfony relies directly on Composer and it would be needed to register this
     * autoloader with Composer autoloader initialization
     *
     * @template RealObjectType of object
     * @param class-string<RealObjectType> $className
     * @param array<string, mixed> $proxyOptions @codingStandardsIgnoreLine
     * @return class-string<RealObjectType>
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @psalm-suppress PossiblyUnusedReturnValue
     */
    protected function generateProxy(string $className, array $proxyOptions = []): string
    {
        if (array_key_exists($className, $this->checkedClasses)) {
            $generatedClassName = $this->checkedClasses[$className];
            assert(is_a($generatedClassName, $className, true));

            return $generatedClassName;
        }

        $proxyParameters = [
            'className' => $className,
            'factory' => self::class,
            'proxyManagerVersion' => Version::getVersion(),
            'proxyOptions' => $proxyOptions,
        ];

        $proxyClassName = $this
            ->configuration
            ->getClassNameInflector()
            ->getProxyClassName($className, $proxyParameters);

        if (class_exists($proxyClassName)) {
            return $this->checkedClasses[$className] = $proxyClassName;
        }

        $autoloader = $this->configuration->getProxyAutoloader();

        if ($autoloader($proxyClassName)) {
            return $this->checkedClasses[$className] = $proxyClassName;
        }

        return $this->checkedClasses[$className] = parent::generateProxy($className, $proxyOptions);
    }

    private function generateClassMethodsList(): ServicesMethods
    {
        return $this->methodExtractor->extractAll();
    }

    private function generateProxies(ServicesMethods $repositoriesMethods): void
    {
        $classNames = $repositoriesMethods->getClassNames();

        foreach ($classNames as $className) {
            $this->generateProxy($className);
        }
    }
}
