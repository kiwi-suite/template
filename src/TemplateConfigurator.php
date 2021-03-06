<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Template;

use Ixocreate\Application\Configurator\ConfiguratorInterface;
use Ixocreate\Application\Service\ServiceRegistryInterface;
use Ixocreate\Application\ServiceManager\SubManagerConfigurator;
use Ixocreate\ServiceManager\Factory\AutowireFactory;
use Ixocreate\Template\Exception\InvalidDirectoryException;
use Ixocreate\Template\Extension\ExtensionInterface;
use Ixocreate\Template\Extension\ExtensionMapping;
use Ixocreate\Template\Extension\ExtensionSubManager;

final class TemplateConfigurator implements ConfiguratorInterface
{
    /**
     * @var SubManagerConfigurator
     */
    private $subManagerConfigurator;

    /**
     * @var string
     */
    private $fileExtension = 'phtml';

    /**
     * @var array
     */
    private $directories = [];

    /**
     * MiddlewareConfigurator constructor.
     */
    public function __construct()
    {
        $this->subManagerConfigurator = new SubManagerConfigurator(
            ExtensionSubManager::class,
            ExtensionInterface::class
        );
    }

    /**
     * @param string $directory
     * @param bool $recursive
     */
    public function addExtensionDirectory(string $directory, bool $recursive = true): void
    {
        $this->subManagerConfigurator->addDirectory($directory, $recursive);
    }

    /**
     * @param string $action
     * @param string $factory
     */
    public function addExtension(string $action, string $factory = AutowireFactory::class): void
    {
        $this->subManagerConfigurator->addFactory($action, $factory);
    }

    /**
     * @param string $name
     * @param string $directory
     * @throws \Exception
     */
    public function addDirectory(string $name, string $directory): void
    {
        if (!\is_dir($directory)) {
            throw new InvalidDirectoryException(\sprintf("template directory %s doesnt exist", $directory));
        }
        $this->directories[$name] = [
            'name' => $name,
            'directory' => $directory,
        ];
    }

    /**
     * @return array
     */
    public function getDirectories(): array
    {
        return $this->directories;
    }

    /**
     * @param string $fileExtension
     */
    public function setFileExtension(string $fileExtension): void
    {
        $this->fileExtension = $fileExtension;
    }

    /**
     * @return string
     */
    public function getFileExtension(): string
    {
        return $this->fileExtension;
    }

    /**
     * @param ServiceRegistryInterface $serviceRegistry
     */
    public function registerService(ServiceRegistryInterface $serviceRegistry): void
    {
        $factories = $this->subManagerConfigurator->getServiceManagerConfig()->getFactories();

        $extensionMapping = [];
        foreach ($factories as $id => $factory) {
            if (!\is_subclass_of($id, ExtensionInterface::class, true)) {
                throw new \InvalidArgumentException(\sprintf(
                    "'%s' doesn't implement '%s'",
                    $id,
                    ExtensionInterface::class
                ));
            }
            $extensionName = \forward_static_call([$id, 'getName']);
            $extensionMapping[$extensionName] = $id;
        }

        $serviceRegistry->add(ExtensionMapping::class, new ExtensionMapping($extensionMapping));
        $serviceRegistry->add(TemplateConfig::class, new TemplateConfig($this));
        $this->subManagerConfigurator->registerService($serviceRegistry);
    }
}
