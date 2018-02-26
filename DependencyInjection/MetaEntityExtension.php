<?php
declare(strict_types=1);

namespace K3ssen\MetaEntityBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class MetaEntityExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        //ProcessConfiguration to throw exceptions if values are invalid, but do not use its return value.
        $this->processConfiguration($configuration, $configs);
        //The processConfiguration doesn't merge the way we want it
        //Here, we merge the configs ourselves using array_replace_recursive
        $config = [];
        foreach (array_reverse($configs) as $configPart) {
            $config = array_replace_recursive($config, $configPart);
        }

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        foreach ($config as $key => $value) {
            $container->setParameter('meta_entity.'.$key, $value);
        }

        $defaultAttributes = $container->getParameter('default_attributes');
        $configuredAttributes = $container->getParameter('meta_entity.attributes');
        $attributesMerged = array_merge_recursive($defaultAttributes, $configuredAttributes);
        $attributes = array_replace_recursive($defaultAttributes, $configuredAttributes);

        foreach ($attributes as $name => $attributeInfo) {
            //meta_properties can only be added, not replaced, so we use the merged-value instead
            $attributeInfo['meta_properties'] = $attributesMerged[$name]['meta_properties'];

            if (isset($defaultAttributes[$name]['type']) && $defaultAttributes[$name]['type'] !== $attributeInfo['type']) {
                throw new InvalidConfigurationException(sprintf('
                    Invalid configuration "meta_entity.attributes.%s"; "type" is set to "%s", but "%s" is required by MetaEntityBundle. Remove "type" from this configuration or change this its value to "%s"
                ', $name, $attributeInfo['type'], $defaultAttributes[$name]['type'], $defaultAttributes[$name]['type']));
            }
        }

        $container->setParameter('meta_entity.attributes', $attributes);
    }
}
