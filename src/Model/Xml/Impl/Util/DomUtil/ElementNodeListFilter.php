<?php

namespace BpmPlatform\Model\Xml\Impl\Util\DomUtil;

class ElementNodeListFilter implements NodeListFilterInterface
{
    public function matches(\DomElement $element): bool
    {
        return $element->nodeType == XML_ELEMENT_NODE;
    }
}
