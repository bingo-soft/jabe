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

    /**
     * @param mixed $element
     */
    public function matches($element): bool
    {
        return parent::matches($element) &&
               $this->localName == $element->localName &&
               $this->namespaceUri == $element->namespaceURI;
    }
}
