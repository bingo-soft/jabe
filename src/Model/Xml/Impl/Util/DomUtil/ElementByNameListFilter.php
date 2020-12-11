<?php

namespace BpmPlatform\Model\Xml\Impl\Util\DomUtil;

class ElementByNameListFilter extends ElementNodeListFilter
{
    private $localName;
    private $namespaceUri;

    public function __construct(string $localName, string $namespaceUri)
    {
        $this->localName = $localName;
        $this->namespaceUri = $namespaceUri;
    }

    public function matches(\DomElement $element): bool
    {
        return parent::matches($element) &&
               $this->localName == $element->localName &&
               $this->namespaceUri == $element->namespaceURI;
    }
}
