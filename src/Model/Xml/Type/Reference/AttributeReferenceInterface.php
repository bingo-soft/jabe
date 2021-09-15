<?php

namespace BpmPlatform\Model\Xml\Type\Reference;

use BpmPlatform\Model\Xml\Type\Attribute\AttributeInterface;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;

interface AttributeReferenceInterface extends ReferenceInterface
{
    public function getReferenceSourceAttribute(): AttributeInterface;
}
