<?php
declare(strict_types=1);

namespace K3ssen\MetaEntityBundle;

use K3ssen\MetaEntityBundle\DependencyInjection\Compiler\MetaEntityCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MetaEntityBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new MetaEntityCompilerPass());
    }
}
