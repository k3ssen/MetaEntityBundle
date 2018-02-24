<?php
declare(strict_types=1);

namespace K3ssen\MetaEntityBundle\MetaData\Property;

use Doctrine\DBAL\Types\Type;

class TimeMetaProperty extends AbstractPrimitiveMetaProperty implements TimeMetaPropertyInterface
{
    public const ORM_TYPE = Type::TIME;
    public const ORM_TYPE_ALIAS = 'time';
    public const RETURN_TYPE = '\DateTime';
}
