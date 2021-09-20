<?php

namespace BpmPlatform\Model\Xml\Impl\Instance;

use BpmPlatform\Model\Xml\Impl\ModelInstanceImpl;
use BpmPlatform\Model\Xml\Instance\{
    DomDocumentInterface,
    DomElementInterface,
    ModelElementInstanceInterface
};
use BpmPlatform\Model\Xml\Impl\Util\XmlQName;
use BpmPlatform\Model\Xml\Impl\Util\DomUtil;

class DomElementImpl implements DomElementInterface
{
    private const MODEL_ELEMENT_KEY = "example.modelElementRef";
    private const XMLNS_ATTRIBUTE_NS_URI = "http://www.w3.org/2000/xmlns/";
    private const XMLNS_ATTRIBUTE = "xmlns";

    private $element;

    private $document;

    private $modelElementInstance;

    public function __construct(\DOMElement $element)
    {
        $this->element = $element;
        $this->document = $element->ownerDocument;
    }

    public function getElement(): \DOMElement
    {
        return $this->element;
    }

    public function getNamespaceURI(): string
    {
        return $this->element->namespaceURI;
    }

    public function getLocalName(): string
    {
        return $this->element->localName;
    }

    public function getPrefix(): string
    {
        return $this->element->prefix;
    }

    public function getDocument(): ?DomDocumentInterface
    {
        $ownerDocument = $this->element->ownerDocument;
        if ($ownerDocument != null) {
            return new DomDocumentImpl($ownerDocument);
        } else {
            return null;
        }
    }

    public function getRootElement(): ?DomElementInterface
    {
        $document = $this->getDcoument();
        if ($document != null) {
            return $document->getRootElement();
        } else {
            return null;
        }
    }

    public function getParentElement(): ?DomElementInterface
    {
        $parentNode = $this->element->parentNode;
        if ($parentNode != null && $parentNode instanceof \DOMElement) {
            return new DomElementImpl($parentNode);
        } else {
            return null;
        }
    }

    public function getChildElements(): array
    {
        $childNodes = $this->element->childNodes;
        return DomUtil::filterNodeListForElements($childNodes);
    }

    /**
     * @param mixed $uris
     * @param string $elementName
     */
    public function getChildElementsByNameNs($uris, string $elementName): array
    {
        $childNodes = $this->element->childNodes;
        $result = [];
        if (is_string($uris)) {
            $result = DomUtil::filterNodeListByName($childNodes, $uris, $elementName);
        } elseif (is_array($uris)) {
            foreach ($uris as $uri) {
                $result = array_merge($result, DomUtil::filterNodeListByName($childNodes, $uri, $elementName));
            }
        }
        return $result;
    }


    public function getChildElementsByType(
        ModelInstanceImpl $modelInstance,
        string $elementType
    ): array {
        $childNodes = $this->element->childNodes;
        return DomUtil::filterNodeListByType($childNodes, $modelInstance, $elementType);
    }

    public function replaceChild(
        DomElementInterface $newChildDomElement,
        DomElementInterface $existingChildDomElement
    ): void {
        $newElement = $newChildDomElement->getElement();
        $existingElement = $existingChildDomElement->getElement();
        if ($this->element->replaceChild($newElement, $existingElement) === false) {
            throw new \DOMException("Unable to replace child element with new element");
        }
    }

    public function removeChild(DomElementInterface $childDomElement): bool
    {
        $childElement = $childDomElement->getElement();
        try {
            $this->element->removeChild($childElement);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function appendChild(DomElementInterface $childDomElement): void
    {
        $childElement = $childDomElement->getElement();
        $this->element->appendChild($childElement);
    }

    public function insertChildElementAfter(
        DomElementInterface $elementToInsert,
        ?DomElementInterface $insertAfter
    ): void {
        $newElement = $elementToInsert->getElement();
        if ($insertAfter == null) {
            $insertBeforeNode = $this->element->firstChild;
        } else {
            $insertBeforeNode = $insertAfter->getElement()->nextSibling;
        }

        if ($insertBeforeNode != null) {
            $this->element->insertBefore($newElement, $insertBeforeNode);
        } else {
            $this->element->appendChild($newElement);
        }
    }

    public function hasAttribute(?string $namespaceUri, string $localName): bool
    {
        if ($namespaceUri == null) {
            return $this->element->hasAttribute($localName);
        } else {
            return $this->element->hasAttributeNS($namespaceUri, $localName);
        }
    }

    public function getAttribute(?string $namespaceUri, string $localName): ?string
    {
        $xmlQName = new XmlQName($this->getDocument(), $this, $namespaceUri, $localName);
        if ($xmlQName->hasLocalNamespace()) {
            $value = $this->element->getAttribute($xmlQName->getLocalName());
        } else {
            $value = $this->element->getAttributeNS($xmlQName->getNamespaceUri(), $xmlQName->getLocalName());
        }

        if (empty($value)) {
            return null;
        } else {
            return $value;
        }
    }

    public function setAttribute(?string $namespaceUri, string $localName, string $value, ?bool $isIdAttribute = false): void
    {
        $isIdAttribute = $isIdAttribute ?? false;
        $xmlQName = new XmlQName($this->getDocument(), $this, $namespaceUri, $localName);
        if ($xmlQName->hasLocalNamespace()) {
            $this->element->setAttribute($xmlQName->getLocalName(), $value);
            if ($isIdAttribute && !$this->element->getAttributeNode("id")->isId()) {
                $this->element->setIdAttribute($xmlQName->getLocalName(), true);
            }
        } else {
            $this->element->setAttributeNS($xmlQName->getNamespaceUri(), $xmlQName->getPrefixedName(), $value);
            if ($isIdAttribute && !$this->element->getAttributeNodeNS($xmlQName->getNamespaceUri(), "id")->isId()) {
                $this->element->setIdAttributeNS($xmlQName->getNamespaceUri(), $xmlQName->getLocalName(), true);
            }
        }
    }

    public function setIdAttribute(?string $namespaceUri, string $localName, string $value): void
    {
        $namespaceUri = $namespaceUri ?? $this->getNamespaceURI();
        $this->setAttribute($namespaceUri, $localName, $value, true);
    }

    public function removeAttribute(?string $namespaceUri, string $localName): void
    {
        $namespaceUri = $namespaceUri ?? $this->getNamespaceURI();
        $xmlQName = new XmlQName($this->getDocument(), $this, $namespaceUri, $localName);
        if ($xmlQName->hasLocalNamespace()) {
            $this->element->removeAttribute($xmlQName->getLocalName());
        } else {
            $this->element->removeAttributeNS($xmlQName->getNamespaceUri(), $xmlQName->getLocalName());
        }
    }

    public function getTextContent(): string
    {
        return $this->element->textContent;
    }

    public function setTextContent(string $textContent): void
    {
        $this->element->nodeValue = $textContent;
    }

    public function addCDataSection(string $data): void
    {
        $document = $this->getDocument();
        $cdataSection = $document->createCDATASection($data);
        $this->element->appendChild($cdataSection);
    }

    public function getModelElementInstance(): ?ModelElementInstanceInterface
    {
        return $this->modelElementInstance;
    }

    public function setModelElementInstance(ModelElementInstanceInterface $modelElementInstance): void
    {
        $this->modelElementInstance = $modelElementInstance;
    }

    /**
     * @return mixed
     */
    public function registerNamespace(?string $prefix, string $namespaceUri)
    {
        if ($prefix != null) {
            $this->element->setAttributeNS(
                self::XMLNS_ATTRIBUTE_NS_URI,
                self::XMLNS_ATTRIBUTE . ":" . $prefix,
                $namespaceUri
            );
        } else {
            $lookupPrefix = $this->lookupPrefix($namespaceUri);
            if ($lookupPrefix == null) {
                if (array_key_exists($namespaceUri, XmlQName::KNOWN_PREFIXES)) {
                    $prefix = XmlQName::KNOWN_PREFIXES[$namespaceUri];
                }
                if (
                    $prefix != null &&
                    $this->getRootElement() != null &&
                    $this->getRootElement()->hasAttributeNS(self::XMLNS_ATTRIBUTE_NS_URI, $prefix)
                ) {
                    $prefix = null;
                }
                if ($prefix == null) {
                    $prefix = $this->getDocument()->getUnusedGenericNsPrefix();
                }
                $this->registerNamespace($prefix, $namespaceUri);
                return $prefix;
            } else {
                return $lookupPrefix;
            }
        }
    }

    public function lookupPrefix(string $namespaceUri): ?string
    {
        return $this->element->lookupPrefix($namespaceUri);
    }

    public function equals(?DomElementInterface $obj): bool
    {
        if ($obj == null) {
            return false;
        }

        return $this->element->isSameNode($obj->getElement());
    }
}
