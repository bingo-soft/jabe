<?php

namespace BpmPlatform\Model\Xml\Impl\Instance;

use BpmPlatform\Model\Xml\Instance\{
    DomElementInterface,
    DomDocumentInterface
};
use BpmPlatform\Model\Xml\Impl\Util\XmlQName;
use BpmPlatform\Model\Xml\Impl\Util\DomUtil\DomUtils;
use BpmPlatform\Model\Xml\Exception\ModelException;

class DomDocumentImpl implements DomDocumentInterface
{
    public const GENERIC_NS_PREFIX = "ns";
    public const XMLNS_ATTRIBUTE_NS_URI = "http://www.w3.org/2000/xmlns/";

    private $document;

    public function __construct(\DOMDocument $document)
    {
        $this->document = $document;
    }

    public function getRootElement(): ?DomElementInterface
    {
        $documentElement = $this->document->documentElement;
        if ($documentElement != null) {
            return new DomElementImpl($documentElement);
        } else {
            return null;
        }
    }

    public function setRootElement(DomElementInterface $rootElement): void
    {
        $documentElement = $this->document->documentElement;
        $newDocumentElement = $rootElement->getElement();
        if ($documentElement != null) {
            $this->document->replaceChild($newDocumentElement, $documentElement);
        } else {
            $this->document->appendChild($newDocumentElement);
        }
    }

    public function createElement(string $namespaceUri, string $localName): DomElementInterface
    {
        $xmlQName = new XmlGName($this, $namespaceUri, $localName);
        $element = $this->document->createElementNS($xmlQName->getNamespaceUri(), $xmlQName->getPrefixedName());
        return new DomElementImpl($element);
    }

    public function getElementById(string $id): ?DomDocumentInterface
    {
        $element = $this->document->getElementById($id);
        if ($element != null) {
            return new DomElementImpl($element);
        } else {
            return null;
        }
    }

    public function getElementsByNameNs(string $namespaceUri, string $localName): array
    {
        $elementsByTagNameNS = $this->document->getElementsByTagNameNS($namespaceUri, $localName);
        return DomUtils::filterNodeListByName($elementsByTagNameNS, $namespaceUri, $localName);
    }

    public function registerNamespace(?string $prefix, string $namespaceUri): void
    {
        $rootElement = $this->getRootElement();
        if ($rootElement != null) {
            $rootElement->registerNamespace($prefix, $namespaceUri);
        } else {
            throw new ModelException("Unable to define a new namespace without a root document element");
        }
    }

    public function getUnusedGenericNsPrefix(): string
    {
        $documentElement = $this->document->documentElement;
        if ($documentElement == null) {
            return self::GENERIC_NS_PREFIX . "0";
        } else {
            for ($i = 0; $i < PHP_INT_MAX; $i += 1) {
                if (!$documentElement->hasAttributeNS(self::XMLNS_ATTRIBUTE_NS_URI, self::GENERIC_NS_PREFIX . $i)) {
                    return self::GENERIC_NS_PREFIX . $i;
                }
            }
        }
    }

    public function clone(): DomDocumentInterface
    {
        $xml = $this->document->saveXML();
        $clone = new \DOMDocument();
        $clone->loadXML($xml);
        return new DomDocumentImpl($clone);
    }
}
