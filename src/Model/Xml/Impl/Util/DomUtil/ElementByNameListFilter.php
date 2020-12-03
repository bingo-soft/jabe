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

    public function matches(\DomNode $node): bool
    {
        return parent::matches($node) &&
               $this->localName == $node->localName &&
               $this->namespaceUri == $node->namespaceURI;
    }
}
