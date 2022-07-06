<?php

namespace Jabe\Engine\Impl\Util\El;

class MethodInfo
{
    private $name;
    private $returnType;
    private $paramTypes = [];

    /**
     * Creates a new instance of MethodInfo with the given information.
     *
     * @param name
     *            The name of the method
     * @param returnType
     *            The return type of the method
     * @param paramTypes
     *            The types of each of the method's parameters
     */
    public function __construct(string $name, ?string $returnType = null, ?array $paramTypes = [])
    {
        $this->name = $name;
        $this->returnType = $returnType;
        $this->paramTypes = $paramTypes;
    }

    /**
     * Returns the name of the method
     *
     * @return string the name of the method
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns the parameter types of the method
     *
     * @return array the parameter types of the method
     */
    public function getParamTypes(): array
    {
        return $this->paramTypes;
    }

    /**
     * Returns the return type of the method
     *
     * @return string the return type of the method
     */
    public function getReturnType(): string
    {
        return $this->returnType;
    }
}
