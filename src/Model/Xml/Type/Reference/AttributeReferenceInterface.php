<?php

namespace Jabe\Model\Xml\Type\Reference;

use Jabe\Model\Xml\Type\Attribute\AttributeInterface;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;

interface AttributeReferenceInterface extends ReferenceInterface
{
    public function getReferenceSourceAttribute(): AttributeInterface;
}
