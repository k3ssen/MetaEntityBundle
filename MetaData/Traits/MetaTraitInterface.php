<?php
declare(strict_types=1);

namespace K3ssen\MetaEntityBundle\MetaData\Traits;


use K3ssen\MetaEntityBundle\MetaData\MetaEntityInterface;

interface MetaTraitInterface
{
    public function __construct(MetaEntityInterface $metaEntity);

    public function getName(): string;

    public function getNamespace(): string;
}