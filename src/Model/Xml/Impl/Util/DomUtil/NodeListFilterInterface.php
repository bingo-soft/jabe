<?php

namespace BpmPlatform\Model\Xml\Impl\Util\DomUtil;

interface NodeListFilterInterface
{
    /**
     * @param mixed $element
     */
    public function matches($element): bool;
}
