<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance\Dc;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Impl\Instance\BpmnModelElementInstanceImpl;
use BpmPlatform\Model\Bpmn\Instance\Dc\FontInterface;

class FontImpl extends BpmnModelElementInstanceImpl implements FontInterface
{
    protected static $nameAttribute;
    protected static $sizeAttribute;
    protected static $isBoldAttribute;
    protected static $isItalicAttribute;
    protected static $isUnderlineAttribute;
    protected static $isStrikeTroughAttribute;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            FontInterface::class,
            BpmnModelConstants::DC_ELEMENT_FONT
        )
        ->namespaceUri(BpmnModelConstants::DC_NS)
        ->instanceProvider(
            new class extends ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new FontImpl($instanceContext);
                }
            }
        );

        self::$nameAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::DC_ATTRIBUTE_NAME)
        ->build();

        self::$sizeAttribute = $typeBuilder->doubleAttribute(BpmnModelConstants::DC_ATTRIBUTE_SIZE)
        ->build();

        self::$isBoldAttribute = $typeBuilder->booleanAttribute(BpmnModelConstants::DC_ATTRIBUTE_IS_BOLD)
        ->build();

        self::$isItalicAttribute = $typeBuilder->booleanAttribute(BpmnModelConstants::DC_ATTRIBUTE_IS_ITALIC)
        ->build();

        self::$isUnderlineAttribute = $typeBuilder->booleanAttribute(BpmnModelConstants::DC_ATTRIBUTE_IS_UNDERLINE)
        ->build();

        self::$isStrikeTroughAttribute = $typeBuilder->booleanAttribute(
            BpmnModelConstants::DC_ATTRIBUTE_IS_STRIKE_THROUGH
        )
        ->build();

        $typeBuilder->build();
    }

    public function getName(): string
    {
        return self::$nameAttribute->getValue($this);
    }

    public function setName(string $name): void
    {
        self::$nameAttribute->setValue($this, $name);
    }

    public function getSize(): float
    {
        return self::$sizeAttribute->getValue($this);
    }

    public function setSize(float $size): void
    {
        self::$sizeAttribute->setValue($this, $size);
    }

    public function isBold(): bool
    {
        return self::$isBoldAttribute->getValue($this);
    }

    public function setBold(bool $isBold): void
    {
        self::$isBoldAttribute->setValue($this, $isBold);
    }

    public function isItalic(): bool
    {
        return self::$isItalicAttribute->getValue($this);
    }

    public function setItalic(bool $isItalic): void
    {
        self::$isItalicAttribute->setValue($this, $isItalic);
    }

    public function isUnderline(): bool
    {
        return self::$isUnderlineAttribute->getValue($this);
    }

    public function setUnderline(bool $isUnderline): void
    {
        self::$isUnderlineAttribute->setValue($this, $isUnderline);
    }

    public function isStrikeThrough(): bool
    {
        return self::$isStrikeTroughAttribute->getValue($this);
    }

    public function setStrikeTrough(bool $isStrikeTrough): void
    {
        self::$isStrikeTroughAttribute->setValue($this, $isStrikeTrough);
    }
}
