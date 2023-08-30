<?php

namespace Jabe\Impl\Bpmn\Parser;

use Jabe\{
    BpmnParseException,
    ProcessEngineException
};
use Jabe\Impl\Context\Context;
use Jabe\Impl\Core\Variable\Mapping\{
    InputParameter,
    IoMapping,
    OutputParameter
};
use Jabe\Impl\Core\Variable\Mapping\Value\{
    ListValueProvider,
    MapValueProvider,
    NullValueProvider,
    ParameterValueProviderInterface
};
use Jabe\Impl\El\{
    ElValueProvider,
    ExpressionManagerInterface
};
use Jabe\Impl\Scripting\{
    ExecutableScript,
    ScriptValueProvider
};
use Jabe\Impl\Util\ScriptUtil;
use Sax\Element;

class BpmnParseUtil
{
    /**
     * Returns the extension element in the extension namespace
     * and the given name.
     *
        * @param element the parent element of the extension element
    * @param extensionElementName the name of the extension element to find
    * @return Element the extension element or null if not found
    */
    public static function findExtensionElement(Element $element, ?string $extensionElementName): ?Element
    {
        $extensionElements = $element->element("extensionElements");
        if (!empty($extensionElements)) {
            return $extensionElements->element($extensionElementName);
        }
        return null;
    }

    /**
     * Returns the IoMapping of an element.
     *
     * @param element the element to parse
     * @return IoMapping the input output mapping or null if non defined
     * @throws BpmnParseException if a input/output parameter element is malformed
     */
    public static function parseInputOutput(Element $element): ?IoMapping
    {
        $inputOutputElement = $element->elementNS(BpmnParser::BPMN_EXTENSIONS_NS, "inputOutput");
        if ($inputOutputElement !== null) {
            $ioMapping = new IoMapping();
            self::parseInputParameters($inputOutputElement, $ioMapping);
            self::parseOutputParameters($inputOutputElement, $ioMapping);
            return $ioMapping;
        }
        return null;
    }

    /**
     * Parses all input parameters of an input output element and adds them to
     * the IoMapping.
     *
     * @param inputOutputElement the input output element to process
     * @param ioMapping the input output mapping to add input parameters to
     * @throws BpmnParseException if a input parameter element is malformed
     */
    public static function parseInputParameters(Element $inputOutputElement, IoMapping $ioMapping): void
    {
        $inputParameters = $inputOutputElement->elements(BpmnParse::BPMN_EXTENSIONS_NS_PREFIX . ":inputParameter");
        foreach ($inputParameters as $inputParameterElement) {
            self::parseInputParameterElement($inputParameterElement, $ioMapping);
        }
    }

    /**
     * Parses all output parameters of an input output element and adds them to
     * the IoMapping.
     *
     * @param inputOutputElement the input output element to process
     * @param ioMapping the input output mapping to add input parameters to
     * @throws BpmnParseException if a output parameter element is malformed
     */
    public static function parseOutputParameters(Element $inputOutputElement, IoMapping $ioMapping): void
    {
        $outputParameters = $inputOutputElement->elementsNS(BpmnParser::BPMN_EXTENSIONS_NS, "outputParameter");
        foreach ($outputParameters as $outputParameterElement) {
            self::parseOutputParameterElement($outputParameterElement, $ioMapping);
        }
    }

    /**
     * Parses a input parameter and adds it to the IoMapping.
     *
     * @param inputParameterElement the input parameter element
     * @param ioMapping the mapping to add the element
     * @throws BpmnParseException if the input parameter element is malformed
     */
    public static function parseInputParameterElement(Element $inputParameterElement, IoMapping $ioMapping): void
    {
        $nameAttribute = $inputParameterElement->attribute("name");
        if (empty($nameAttribute)) {
            throw new BpmnParseException("Missing attribute 'name' for inputParameter");
        }

        $valueProvider = self::parseNestedParamValueProvider($inputParameterElement);
        // add parameter
        $ioMapping->addInputParameter(new InputParameter($nameAttribute, $valueProvider));
    }

    /**
     * Parses a output parameter and adds it to the IoMapping.
     *
     * @param outputParameterElement the output parameter element
     * @param ioMapping the mapping to add the element
     * @throws BpmnParseException if the output parameter element is malformed
     */
    public static function parseOutputParameterElement(Element $outputParameterElement, IoMapping $ioMapping): void
    {
        $nameAttribute = $outputParameterElement->attribute("name");
        if (empty($nameAttribute)) {
            throw new BpmnParseException("Missing attribute 'name' for outputParameter");
        }

        $valueProvider = self::parseNestedParamValueProvider($outputParameterElement);

        // add parameter
        $ioMapping->addOutputParameter(new OutputParameter($nameAttribute, $valueProvider));
    }

    /**
     * @throws BpmnParseException if the parameter is invalid
     */
    protected static function parseNestedParamValueProvider(Element $element): ParameterValueProviderInterface
    {
        // parse value provider
        if (count($element->elements()) == 0) {
            return self::parseParamValueProvider($element);
        } elseif (count($element->elements()) == 1) {
            return self::parseParamValueProvider($element->elements()[0]);
        } else {
            throw new BpmnParseException("Nested parameter can at most have one child element");
        }
    }

    /**
     * @throws BpmnParseException if the parameter is invalid
     */
    protected static function parseParamValueProvider(Element $parameterElement): ParameterValueProviderInterface
    {
        // LIST
        if ($parameterElement->getTagName() == "list") {
            $providerList = [];
            foreach ($parameterElement->elements() as $element) {
                // parse nested provider
                $providerList[] = self::parseParamValueProvider($element);
            }
            return new ListValueProvider($providerList);
        }

        // MAP
        if ($parameterElement->getTagName() == "map") {
            $providerMap = [];
            foreach ($parameterElement->elements("entry") as $entryElement) {
                // entry must provide key
                $keyAttribute = $entryElement->attribute("key");
                if (empty($keyAttribute)) {
                    throw new BpmnParseException("Missing attribute 'key' for 'entry' element");
                }
                // parse nested provider
                $providerMap[] = [
                    new ElValueProvider(self::getExpressionManager()->createExpression($keyAttribute)),
                    self::parseNestedParamValueProvider($entryElement)
                ];
            }
            return new MapValueProvider($providerMap);
        }

        // SCRIPT
        if ($parameterElement->getTagName() == "script") {
            $executableScript = self::parseScript($parameterElement);
            if ($executableScript !== null) {
                return new ScriptValueProvider($executableScript);
            } else {
                return new NullValueProvider();
            }
        }

        $textContent = trim($parameterElement->getText());
        if (!empty($textContent)) {
            // EL
            return new ElValueProvider(self::getExpressionManager()->createExpression($textContent));
        } else {
            // NULL value
            return new NullValueProvider();
        }
    }

    /**
     * Parses a script element.
     *
     * @param scriptElement the script element ot parse
     * @return ExecutableScript the generated executable script
     * @throws BpmnParseException if the a attribute is missing or the script cannot be processed
     */
    public static function parseScript(Element $scriptElement): ExecutableScript
    {
        $scriptLanguage = $scriptElement->attribute("scriptFormat");
        if (empty($scriptLanguage)) {
            throw new BpmnParseException("Missing attribute 'scriptFormat' for 'script' element");
        } else {
            $scriptResource = $scriptElement->attribute("resource");
            $scriptSource = $scriptElement->getText();
            try {
                return ScriptUtil::getScript($scriptLanguage, $scriptSource, $scriptResource, self::getExpressionManager());
            } catch (ProcessEngineException $e) {
                throw new BpmnParseException("Unable to process script");
            }
        }
    }

    public static function parseExtensionProperties(Element $element): array
    {
        $propertiesMap = [];
        $propertiesElement = self::findExtensionElement($element, "properties");
        if ($propertiesElement !== null) {
            $properties = $propertiesElement->elements("property");
            foreach ($properties as $property) {
                $propertiesMap[$property->attribute("name")] = $property->attribute("value");
            }
        }
        return $propertiesMap;
    }

    protected static function getExpressionManager(): ExpressionManagerInterface
    {
        return Context::getProcessEngineConfiguration()->getExpressionManager();
    }
}
