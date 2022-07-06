<?php

namespace Tests\Xml\TestModel\Instance;

use Jabe\Model\Xml\Impl\Util\StringUtil;
use Jabe\Model\Xml\ModelInstanceInterface;
use Jabe\Model\Xml\Impl\Parser\AbstractModelParser;
use Tests\Xml\TestModel\{
    Gender,
    TestModelConstants,
    TestModelParser
};
use Tests\Xml\TestModel\TestModelTest;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;
use Jabe\Model\Xml\Impl\Instance\ModelElementInstanceImpl;

class AlternativeNsTest extends TestModelTest
{
    private const MECHANICAL_NS = "http://test.org/mechanical";
    private const YET_ANOTHER_NS = "http://test.org/yans";

    protected function setUp(): void
    {
        parent::parseModel("AlternativeNsTest");
        $modelImpl = $this->modelInstance->getModel();
        $modelImpl->declareAlternativeNamespace(self::MECHANICAL_NS, TestModelConstants::NEWER_NAMESPACE);
        $modelImpl->declareAlternativeNamespace(self::YET_ANOTHER_NS, TestModelConstants::NEWER_NAMESPACE);
    }

    protected function tearDown(): void
    {
        $modelImpl = $this->modelInstance->getModel();
        $modelImpl->undeclareAlternativeNamespace(self::MECHANICAL_NS);
        $modelImpl->undeclareAlternativeNamespace(self::YET_ANOTHER_NS);
    }

    public function testGetUniqueChildElementByNameNsForAlternativeNs(): void
    {
        $this->hedwig = $this->modelInstance->getModelElementById("hedwig");
        $this->assertFalse(empty($this->hedwig));
        $childElementByNameNs = $this->hedwig->getUniqueChildElementByNameNs(TestModelConstants::NEWER_NAMESPACE, "wings");
        $this->assertFalse(empty($childElementByNameNs));
        $this->assertEquals("wusch", $childElementByNameNs->getTextContent());
    }

    public function testGetUniqueChildElementByNameNsForSecondAlternativeNs(): void
    {
        // given
        $donald = $this->modelInstance->getModelElementById("donald");

        // when
        $childElementByNameNs = $donald->getUniqueChildElementByNameNs(TestModelConstants::NEWER_NAMESPACE, "wings");

        // then
        $this->assertFalse(empty($childElementByNameNs));
        $this->assertEquals("flappy", $childElementByNameNs->getTextContent());
    }

    public function testGetChildElementsByTypeForAlternativeNs(): void
    {
        $birdo = $this->modelInstance->getModelElementById("birdo");
        $this->assertFalse(empty($birdo));

        $elements = $birdo->getChildElementsByType(Wings::class);
        $this->assertCount(1, $elements);
        $this->assertEquals("zisch", $elements[0]->getTextContent());
    }

    public function testGetChildElementsByTypeForSecondAlternativeNs(): void
    {
        // given
        $donald = $this->modelInstance->getModelElementById("donald");

        // when
        $elements = $donald->getChildElementsByType(Wings::class);

        // then
        $this->assertCount(1, $elements);
        $this->assertEquals("flappy", $elements[0]->getTextContent());
    }

    public function testGetAttributeValueNsForAlternativeNs(): void
    {
        $plucky = $this->modelInstance->getModelElementById("plucky");
        $this->assertFalse(empty($plucky));

        $this->assertFalse($plucky->canHaveExtendedWings());
    }

    public function testGetAttributeValueNsForSecondAlternativeNs(): void
    {
        // given
        $donald = $this->modelInstance->getModelElementById("donald");

        // when
        $extendedWings = $donald->canHaveExtendedWings();

        // then
        $this->assertTrue($donald->canHaveExtendedWings());
    }

    public function testModifyingAttributeWithAlternativeNamespaceKeepsAlternativeNamespace(): void
    {
        $plucky = $this->modelInstance->getModelElementById("plucky");
        $this->assertFalse(empty($plucky));
        //validate old value

        $this->assertFalse($plucky->canHaveExtendedWings());
        //change it
        $plucky->setCanHaveExtendedWings(true);

        $attributeValueNs = $plucky->getAttributeValueNs(self::MECHANICAL_NS, "canHaveExtendedWings");
        $this->assertEquals("true", $attributeValueNs);
    }

    public function testModifyingAttributeWithSecondAlternativeNamespaceKeepsSecondAlternativeNamespace(): void
    {
        // given
        $donald = $this->modelInstance->getModelElementById("donald");

        // when
        $donald->setCanHaveExtendedWings(false);

        // then
        $attributeValueNs = $donald->getAttributeValueNs(self::YET_ANOTHER_NS, "canHaveExtendedWings");
        $this->assertEquals("false", $attributeValueNs);
    }

    public function testModifyingAttributeWithNewNamespaceKeepsNewNamespace(): void
    {
        $bird = $this->createBird($this->modelInstance, "waldo", Gender::MALE);
        $bird->setCanHaveExtendedWings(true);
        $attributeValueNs = $bird->getAttributeValueNs(TestModelConstants::NEWER_NAMESPACE, "canHaveExtendedWings");
        $this->assertEquals("true", $attributeValueNs);
    }

    public function testModifyingElementWithAlternativeNamespaceKeepsAlternativeNamespace(): void
    {
        $birdo = $this->modelInstance->getModelElementById("birdo");
        $this->assertFalse($birdo === null);
        $wings = $birdo->getWings();
        $this->assertFalse($wings === null);
        $wings->setTextContent("kawusch");

        $childElementsByNameNs = $birdo->getDomElement()->getChildElementsByNameNs(self::MECHANICAL_NS, "wings");
        $this->assertCount(1, $childElementsByNameNs);
        $this->assertEquals("kawusch", $childElementsByNameNs[0]->getTextContent());
    }

    public function testModifyingElementWithSecondAlternativeNamespaceKeepsSecondAlternativeNamespace(): void
    {
        // given
        $donald = $this->modelInstance->getModelElementById("donald");
        $wings = $donald->getWings();

        // when
        $wings->setTextContent("kawusch");

        // then
        $childElementsByNameNs = $donald->getDomElement()->getChildElementsByNameNs(self::YET_ANOTHER_NS, "wings");
        $this->assertCount(1, $childElementsByNameNs);
        $this->assertEquals("kawusch", $childElementsByNameNs[0]->getTextContent());
    }

    public function testModifyingElementWithNewNamespaceKeepsNewNamespace(): void
    {
        $bird = $this->createBird($this->modelInstance, "waldo", Gender::MALE);
        $bird->setWings($this->modelInstance->newInstance(Wings::class));

        $childElementsByNameNs = $bird->getDomElement()->getChildElementsByNameNs(TestModelConstants::NEWER_NAMESPACE, "wings");
        $this->assertCount(1, $childElementsByNameNs);
    }

    public function testUseExistingNamespace(): void
    {
        $this->assertThatThereIsNoNewerNamespaceUrl();
        $plucky = $this->modelInstance->getModelElementById("plucky");
        $plucky->setAttributeValueNs(self::MECHANICAL_NS, "canHaveExtendedWings", "true");

        $donald = $this->modelInstance->getModelElementById("donald");
        $donald->setAttributeValueNs(self::YET_ANOTHER_NS, "canHaveExtendedWings", "false");
        $this->assertThatThereIsNoNewerNamespaceUrl();

        $this->assertTrue($plucky->canHaveExtendedWings());
        $this->assertThatThereIsNoNewerNamespaceUrl();
    }

    protected function assertThatThereIsNoNewerNamespaceUrl(): void
    {
        $rootElement = $this->modelInstance->getDocument()->getDomSource()->documentElement;
        foreach ($rootElement->attributes as $attr) {
            $nodeValue = $attr->nodeValue;
            $this->assertFalse(TestModelConstants::NEWER_NAMESPACE == $nodeValue);
        }
    }
}
