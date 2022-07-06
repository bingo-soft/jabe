<?php

namespace Tests\Bpmn\Instance;

use Tests\Xml\Test\{
    AbstractModelElementInstanceTest,
    AbstractTypeAssumption
};
use Jabe\Model\Bpmn\Bpmn;
use Jabe\Model\Xml\Impl\Util\QName;

abstract class BpmnModelElementInstanceTest extends AbstractModelElementInstanceTest
{
    protected $modelInstance;
    protected $model;
    protected $modelElementType;
    protected $namespace = __NAMESPACE__;
    protected $impl = false;

    protected function setUp(): void
    {
        $ref = new \ReflectionClass(static::class);
        $shortName = $ref->getShortName();
        if ($this->impl === false) {
            $className = str_replace('Test', 'Interface', $shortName);
        } else {
            $className = str_replace('Test', '', $shortName);
        }
        $instanceClass = sprintf("%s\%s", str_replace('Tests', 'Jabe\Model', $this->namespace), $className);
        $this->modelInstance = Bpmn::getInstance()->createEmptyModel();
        $this->model = $this->modelInstance->getModel();
        $this->modelElementType = $this->model->getType($instanceClass);
    }

    abstract public function getTypeAssumption(): AbstractTypeAssumption;

    abstract public function getChildElementAssumptions(): array;

    abstract public function getAttributesAssumptions(): array;

    public function testModelNotNull(): void
    {
        $this->assertFalse($this->modelInstance === null);
        $this->assertFalse($this->model === null);
        $this->assertFalse($this->modelElementType === null);
    }

    public function testType(): void
    {
        $this->assertTrue($this->modelElementType->getModel() == $this->model);

        $assumption = $this->getTypeAssumption();

        $this->assertEquals($assumption->namespaceUri, $this->modelElementType->getTypeNamespace());

        if ($assumption->isAbstract) {
            $this->assertTrue($this->modelElementType->isAbstract());
        } else {
            $this->assertFalse($this->modelElementType->isAbstract());
        }

        if ($assumption->extendsType === null) {
            $this->assertNull($this->modelElementType->getBaseType());
        } else {
            $this->assertEquals($assumption->extendsType, $this->modelElementType->getBaseType());
        }

        if ($assumption->isAbstract) {
            try {
                $this->modelInstance->newInstance($this->modelElementType);
            } catch (\Exception $e) {
                $this->assertEquals('Jabe\Model\Xml\Exception\ModelTypeException', get_class($e));
            }
        } else {
            $modelElementInstance = $this->modelInstance->newInstance($this->modelElementType);
            $this->assertFalse($modelElementInstance === null);
        }
    }

    public function testChildElements(): void
    {
        $childElementAssumptions = $this->getChildElementAssumptions();
        if (empty($childElementAssumptions)) {
            $this->assertEmpty($this->getTypeNames($this->modelElementType->getChildElementTypes()));
        } else {
            $this->assertCount(count($childElementAssumptions), $this->modelElementType->getChildElementTypes());
            foreach ($childElementAssumptions as $assumption) {
                $exists = false;
                foreach ($this->modelElementType->getChildElementTypes() as $type) {
                    if ($type->getTypeName() == $assumption->childElementType->getTypeName()) {
                        $exists = true;
                        break;
                    }
                }
                $this->assertTrue($exists);
                if ($assumption->namespaceUri !== null) {
                    $this->assertEquals($assumption->namespaceUri, $assumption->childElementType->getTypeNamespace());
                }
                $coll = $this->modelElementType->getChildElementCollection($assumption->childElementType);
                $this->assertEquals($assumption->minOccurs, $coll->getMinOccurs());
                $this->assertEquals($assumption->maxOccurs, $coll->getMaxOccurs());
            }
        }
    }

    public function testAttributes(): void
    {
        $attributesAssumptions = $this->getAttributesAssumptions();
        if (empty($attributesAssumptions)) {
            $this->assertEmpty($this->getActualAttributeNames());
        } else {
            $this->assertCount(count($attributesAssumptions), $this->modelElementType->getAttributes());
            foreach ($attributesAssumptions as $assumption) {
                $this->assertContains($assumption->attributeName, $this->getActualAttributeNames());
                $attribute = $this->modelElementType->getAttribute($assumption->attributeName);
                $this->assertEquals(
                    $attribute->getOwningElementType()->getTypeName(),
                    $this->modelElementType->getTypeName()
                );

                if ($assumption->namespace !== null) {
                    $this->assertEquals($assumption->namespace, $attribute->getNamespaceUri());
                } else {
                    $this->assertNull($attribute->getNamespaceUri());
                }

                if ($assumption->isIdAttribute) {
                    $this->assertTrue($attribute->isIdAttribute());
                } else {
                    $this->assertFalse($attribute->isIdAttribute());
                }

                if ($assumption->isRequired) {
                    $this->assertTrue($attribute->isRequired());
                } else {
                    $this->assertFalse($attribute->isRequired());
                }

                if ($assumption->defaultValue !== null) {
                    $this->assertFalse($attribute->getDefaultValue() === null);
                } else {
                    $this->assertNull($attribute->getDefaultValue());
                }
            }
        }
    }

    private function getActualAttributeNames(): array
    {
        $actualAttributeNames = [];
        foreach ($this->modelElementType->getAttributes() as $attribute) {
            $actualAttributeNames[] = $attribute->getAttributeName();
        }
        return $actualAttributeNames;
    }

    private function getTypeNames(array $elementTypes): array
    {
        $typeNames = [];
        foreach ($elementTypes as $elementType) {
            $qName = new QName($elementType->getTypeNamespace(), $elementType->getTypeName());
            $typeNames[] = $qName->__toString();
        }
        return $typeNames;
    }
}
