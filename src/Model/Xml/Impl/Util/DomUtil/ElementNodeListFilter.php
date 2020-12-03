<?php

namespace BpmPlatform\Model\Xml\Impl\Util\DomUtil;

class ElementNodeListFilter implements NodeListFilterInterface
{
    public function matches(\DomNode $node): bool
    {
        return $node->nodeType == XML_ELEMENT_NODE;
    }
}
