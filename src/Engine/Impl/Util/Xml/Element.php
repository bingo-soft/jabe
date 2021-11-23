<?php

namespace BpmPlatform\Engine\Impl\Util\Xml;

use BpmPlatform\Engine\ProcessEngineException;

class Element
{
    protected $tagName;

    protected $attributeMap = [];
    protected $line;
    protected $column;
    protected $text = "";
    protected $elements = [];

    public function __construct(string $name, array $attributes, int $line, int $column)
    {
        $this->tagName = $name;

        if (!empty($attributes)) {
            foreach ($attributes as $name => $value) {
                $this->attributeMap[$name] = new Attribute($name, $value);
            }
        }

        $this->line = $line;
        $this->column = $column;
    }

    public function elements(?string $tagName = null): array
    {
        if ($tagName == null) {
            return $this->elements;
        }
        $selectedElements = [];
        foreach ($this->elements as $element) {
            if ($element->getTagName() == $tagName) {
                $selectedElements[] = $element;
            }
        }
        return $selectedElements;
    }

    public function element(string $tagName): ?Element
    {
        $elements = $this->elements($tagName);
        if (empty($elements)) {
            return null;
        } elseif (count($elements) > 1) {
            throw new ProcessEngineException("Parsing exception: multiple elements with tag name " . $this->tagName . " found");
        }
        return $elements[0];
    }

    public function add(Element $element): void
    {
        $this->elements[] = $element;
    }

    public function attribute(string $name, ?string $defaultValue = null): ?string
    {
        if (array_key_exists($name, $this->attributeMap)) {
            return $this->attributeMap[$name];
        }
        return $defaultValue;
    }

    public function attributes(): array
    {
        return array_keys($this->attributeMap);
    }

    public function __toString()
    {
        return "<"  . $this->tagName . "...";
    }

    public function getTagName(): string
    {
        return $this->tagName;
    }

    public function getLine(): int
    {
        return $this->line;
    }

    public function getColumn(): int
    {
        return $this->column;
    }

    public function appendText(string $text): void
    {
        $this->text .= $text;
    }

    public function getText(): string
    {
        return $this->text;
    }

    /**
     * allows to recursively collect the ids of all elements in the tree.
     */
    public function collectIds(array &$ids): void
    {
        $ids[] = $this->attribute("id");
        foreach ($this->elements as $child) {
            $child->collectIds($ids);
        }
    }
}
