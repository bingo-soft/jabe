<?php

namespace BpmPlatform\Engine\Impl\Util\Xml;

class XmlNamespace
{
    private $namespaceUri;
    private $alternativeUri;

    public function __construct(string $namespaceUri, ?string $alternativeUri = null)
    {
        $this->namespaceUri = $namespaceUri;
        $this->alternativeUri = $alternativeUri;
    }

    /**
     * If a namespace has changed over time it could feel responsible for handling
     * the older one.
     *
     * @return
     */
    public function hasAlternativeUri(): bool
    {
        return $this->alternativeUri != null;
    }

    public function getNamespaceUri(): string
    {
        return $this->namespaceUri;
    }

    public function getAlternativeUri(): ?string
    {
        return $this->alternativeUri;
    }

    public function equals(XmlNamespace $obj): bool
    {
        if ($this == $obj) {
            return true;
        }
        if ($obj == null) {
            return false;
        }
        if ($this->namespaceUri == null) {
            if ($obj->namespaceUri != null) {
                return false;
            }
        } elseif ($this->namespaceUri != $obj->namespaceUri) {
            return false;
        }
        return true;
    }
}
