<?php

namespace BpmPlatform\Model\Xml\Impl\Util\DomUtil;

class ElementNodeListFilter implements NodeListFilterInterface
{
    /**
     * @param mixed $element
     */
    public function matches($element): bool
    {
        return $element->nodeType == XML_ELEMENT_NODE;
    }
}
