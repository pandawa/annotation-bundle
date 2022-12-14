<?php

declare(strict_types=1);

namespace Pandawa\Bundle\AnnotationBundle\Plugin;

use Illuminate\Contracts\Config\Repository as Config;
use Pandawa\Component\Foundation\Bundle\Plugin;
use Pandawa\Contracts\DependencyInjection\ServiceRegistryInterface;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
abstract class AnnotationServicePlugin extends Plugin
{
    protected ?string $targetClass = null;
    protected ?string $defaultPath = null;

    public function __construct(
        protected readonly array $directories = [],
        protected array $exclude = [],
        protected array $scopes = [],
    ) {
    }

    public function configure(): void
    {
        if ($this->bundle->getApp()->configurationIsCached()) {
            $this->loadFromConfig();

            return;
        }

        $this->importAnnotations();
        $this->loadFromConfig();
    }

    protected function loadFromConfig(): void
    {
        $this->serviceRegistry()->load(
            $this->config()->get($this->getConfigCacheKey(), [])
        );
    }

    protected function importAnnotations(): void
    {
        $annotationPlugin = new ImportAnnotationPlugin(
            annotationClasses: $this->getAnnotationClasses(),
            directories: $this->getDirectories(),
            classHandler: $this->getHandler(),
            targetClass: $this->targetClass,
            exclude: $this->exclude,
            scopes: $this->scopes,
        );
        $annotationPlugin->setBundle($this->bundle);
        $annotationPlugin->configure();
    }

    protected function serviceRegistry(): ServiceRegistryInterface
    {
        return $this->bundle->getService(ServiceRegistryInterface::class);
    }

    protected function config(): Config
    {
        return $this->bundle->getService('config');
    }

    protected function getDirectories(): array
    {
        if (empty($this->directories)) {
            return [$this->bundle->getPath($this->defaultPath ?? '')];
        }

        return array_map(
            fn(string $path) => $this->bundle->getPath($path),
            $this->directories
        );
    }

    abstract protected function getAnnotationClasses(): array;

    abstract protected function getHandler(): string;

    abstract protected function getConfigCacheKey(): string;
}
