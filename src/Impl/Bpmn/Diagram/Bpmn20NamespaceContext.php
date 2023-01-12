<?php

namespace Jabe\Impl\Bpmn\Diagram;

class Bpmn20NamespaceContext
{
    public const BPMN = "bpmn";
    public const BPMNDI = "bpmndi";
    public const OMGDC = "omgdc";
    public const OMGDI = "omgdi";

    /**
     * This is a protected filed so you can extend that context with your own namespaces if necessary
     */
    protected $namespaceUris = [];

    public function __construct()
    {
        $this->namespaceUris[self::BPMN] = 'http://www.omg.org/spec/BPMN/20100524/MODEL';
        $this->namespaceUris[self::BPMNDI] = 'http://www.omg.org/spec/BPMN/20100524/DI';
        $this->namespaceUris[self::OMGDC] = 'http://www.omg.org/spec/DD/20100524/DI';
        $this->namespaceUris[self::OMGDI] = 'http://www.omg.org/spec/DD/20100524/DC';
    }

    public function getNamespaceURI(?string $prefix): ?string
    {
        if (array_key_exists($prefix, $this->namespaceUris)) {
            return $this->namespaceUris[$prefix];
        }
        return null;
    }

    public function getPrefix(?string $namespaceURI): ?string
    {
        $index = array_search($namespaceURI, $this->namespaceUris);
        if ($index !== false) {
            return $index;
        }
        return null;
    }

    public function getPrefixes(?string $namespaceURI): array
    {
        return self::getKeysByValue($this->namespaceUris, $namespaceURI);
    }

    private static function getKeysByValue(array $map, $value): array
    {
        $keys = [];
        foreach ($map as $key => $mapValue) {
            if ($value == $mapValue) {
                $keys[] = $key;
            }
        }
        return $keys;
    }

    private static function getKeyByValue(array $map, $value): ?string
    {
        $keys = [];
        foreach ($map as $key => $mapValue) {
            if ($value == $mapValue) {
                return $key;
            }
        }
        return null;
    }
}
