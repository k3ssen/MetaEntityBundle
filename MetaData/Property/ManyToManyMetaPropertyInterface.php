<?php
declare(strict_types=1);

namespace K3ssen\MetaEntityBundle\MetaData\Property;

interface ManyToManyMetaPropertyInterface extends RelationMetaPropertyInterface
{
    public const ORM_TYPE = 'ManyToMany';
}
