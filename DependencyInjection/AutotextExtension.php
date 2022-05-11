<?php

declare(strict_types=1);

namespace Evirma\Bundle\AutotextBundle\DependencyInjection;

use Exception;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;

class AutotextExtension extends Extension
{
    /**
     * @param array $configs
     * @param ContainerBuilder $container
     *
     * @return void
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }

    public function getAlias(): string
    {
        return 'autotext';
    }
}
