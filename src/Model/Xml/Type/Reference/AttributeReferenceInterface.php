<?php

namespace BpmPlatform\Model\Xml\Type\Reference;

use BpmPlatform\Model\Xml\Type\Attribute\AttributeInterface;

interface AttributeReferenceInterface extends ReferenceInterface
{
    public function getReferenceSourceAttribute(): AttributeInterface;
}
