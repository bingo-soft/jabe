<?php

namespace Jabe\Engine\Impl\Juel;

use Jabe\Engine\Impl\Util\El\ValueExpression;

class Bindings extends TypeConverter implements \Serializable
{
    private static $NO_FUNCTIONS = [];
    private static $NO_VARIABLES = [];
    private $functions = [];
    private $variables = [];
    private $converter;

    /**
     * Constructor.
     */
    public function __construct(?array $functions = [], ?array $variables = [], ?TypeConverter $converter = null)
    {
        $this->functions = $functions;
        $this->variables = $variables;
        $this->converter = $converter ?? TypeConverter::getDefault();
    }

    /**
     * Get function by index.
     * @param index function index
     * @return method
     */
    public function getFunction(int $index): ?\ReflectionMethod
    {
        if (array_key_exists($index, $this->functions)) {
            return $this->functions[$index];
        }
        return null;
    }

    /**
     * Test if given index is bound to a function.
     * This method performs an index check.
     * @param index identifier index
     * @return bool true if the given index is bound to a function
     */
    public function isFunctionBound(int $index): bool
    {
        return $index >= 0 && $index < count($this->functions);
    }

    /**
     * Get variable by index.
     * @param index identifier index
     * @return value expression
     */
    public function getVariable(int $index): ?ValueExpression
    {
        if (array_key_exists($index, $this->variables)) {
            return $this->variables[$index];
        }
        return null;
    }

    /**
     * Test if given index is bound to a variable.
     * This method performs an index check.
     * @param index identifier index
     * @return bool true if the given index is bound to a variable
     */
    public function isVariableBound(int $index): bool
    {
        return $index >= 0 && $index < count($this->variables);
    }

    /**
     * Apply type conversion.
     * @param value value to convert
     * @param type target type
     * @return converted value
     * @throws ELException
     */
    public function convert($value, string $type)
    {
        return $this->converter->convert($value, $type);
    }

    public function equals($obj): bool
    {
        if ($obj instanceof Bindings) {
            return $this->functions == $obj->functions
                && $this->variables == $obj->variables
                && $this->converter == $obj->converter;
        }
        return false;
    }

    public function serialize()
    {
        $wrappers = [];
        foreach ($this->functions as $function) {
            $wrappers = serialize(new MethodWrapper($function));
        }
        return json_encode([
            'wrappers' => $wrappers,
            'converter' => serialize($this->converter)
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $wrappers = $json->wrappers;
        foreach ($wrappers as $wrapper) {
            $wrapperObj = unserialize($wrapper);
            $this->functions[] = $wrapperObj->method;
        }
        $this->converter = unserialize($json->converter);
    }
}
