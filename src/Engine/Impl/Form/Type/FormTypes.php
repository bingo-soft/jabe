<?php

namespace Jabe\Engine\Impl\Form\Type;

use Jabe\Engine\Impl\Bpmn\Parser\BpmnParse;
use Jabe\Engine\Impl\Form\Handler\DefaultFormHandler;
use Jabe\Engine\Impl\Util\Xml\Element;

class FormTypes
{
    protected $formTypes = [];

    public function addFormType(AbstractFormFieldType $formType): void
    {
        $this->formTypes[$formType->getName()] = $formType;
    }

    public function parseFormPropertyType(Element $formFieldElement, BpmnParse $bpmnParse): ?AbstractFormFieldType
    {
        $formType = null;
        $typeText = $formFieldElement->attribute("type");
        $datePatternText = $formFieldElement->attribute("datePattern");
        if ($typeText == null && DefaultFormHandler::FORM_FIELD_ELEMENT == $formFieldElement->getTagName()) {
            $bpmnParse->addError("form field must have a 'type' attribute", $formFieldElement);
        }
        if ("date" == $typeText && $datePatternText != null) {
            $formType = new DateFormType($datePatternText);
        } elseif ("enum" == $typeText) {
            $values = [];
            foreach ($formFieldElement->elementsNS(BpmnParse::BPMN_EXTENSIONS_NS_PREFIX, "value") as $valueElement) {
                $valueId = $valueElement->attribute("id");
                $valueName = $valueElement->attribute("name");
                $values[$valueId] = $valueName;
            }
            $formType = new EnumFormType($values);
        } elseif ($typeText != null) {
            $formType = null;
            if (array_key_exists($typeText, $this->formTypes)) {
                $formType = $this->formTypes[$typeText];
            }
            if ($formType == null) {
                $bpmnParse->addError("unknown type '" . $typeText . "'", $formFieldElement);
            }
        }
        return $formType;
    }

    public function getFormType(string $name): ?AbstractFormFieldType
    {
        $formType = null;
        if (array_key_exists($name, $this->formTypes)) {
            $formType = $this->formTypes[$name];
        }
        return $formType;
    }
}
