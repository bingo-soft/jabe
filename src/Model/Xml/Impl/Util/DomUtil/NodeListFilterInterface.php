<?php

namespace BpmPlatform\Model\Xml\Impl\Util\DomUtil;

interface NodeListFilterInterface
{
    public function matches(\DomNode $node): bool;
}
