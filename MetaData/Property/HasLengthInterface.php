<?php
declare(strict_types=1);

namespace K3ssen\MetaEntityBundle\MetaData\Property;

interface HasLengthInterface
{
    public function getLength(): ?int;

    public function setLength(?int $length);
}