<?php
declare(strict_types=1);

namespace K3ssen\MetaEntityBundle\MetaData\Property;

interface DecimalMetaPropertyInterface extends PrimitiveMetaPropertyInterface
{
    public function getPrecision(): ?int;

    public function setPrecision(?int $precision);

    public function getScale(): ?int;

    public function setScale(?int $scale);
}
