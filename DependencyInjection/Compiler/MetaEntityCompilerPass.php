<?php
declare(strict_types=1);

namespace K3ssen\MetaEntityBundle\DependencyInjection\Compiler;

use K3ssen\MetaEntityBundle\MetaData\MetaAttributeFactory;
use K3ssen\MetaEntityBundle\MetaData\MetaAttributeInterface;
use K3ssen\MetaEntityBundle\MetaData\MetaEntityFactory;
use K3ssen\MetaEntityBundle\MetaData\MetaEntityInterface;
use K3ssen\MetaEntityBundle\MetaData\MetaPropertyFactory;
use K3ssen\MetaEntityBundle\MetaData\MetaValidationFactory;
use K3ssen\MetaEntityBundle\MetaData\MetaValidationInterface;
use K3ssen\MetaEntityBundle\MetaData\Property\MetaPropertyInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MetaEntityCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        foreach ($container->getDefinitions() as $definition) {
            if ($definition->getClass() && class_exists($definition->getClass(), false)) {
                if (is_subclass_of($definition->getClass(), MetaPropertyInterface::class, true)) {
                    $container->getDefinition(MetaPropertyFactory::class)->addMethodCall('addMetaPropertyClass', [$definition->getClass()]);
                    continue;
                }
                if (is_subclass_of($definition->getClass(), MetaEntityInterface::class, true)) {
                    $container->getDefinition(MetaEntityFactory::class)->addMethodCall('setMetaEntityClass', [$definition->getClass()]);
                    continue;
                }
                if (is_subclass_of($definition->getClass(), MetaAttributeInterface::class, true)) {
                    $container->getDefinition(MetaAttributeFactory::class)->addMethodCall('setMetaAttributeClass', [$definition->getClass()]);
                    continue;
                }
                if (is_subclass_of($definition->getClass(), MetaValidationInterface::class, true)) {
                    $container->getDefinition(MetaValidationFactory::class)->addMethodCall('setMetaValidationClass', [$definition->getClass()]);
                    continue;
                }
            }
        }
    }
}