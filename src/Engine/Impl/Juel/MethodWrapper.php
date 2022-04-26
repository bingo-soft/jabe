<?php

namespace Jabe\Engine\Impl\Juel;

class MethodWrapper implements \Serializable
{
    public $method;
    public $name;
    public $class;
    public $parameterTypes;

    public function __construct(\ReflectionMethod $method)
    {
        $this->method = $method;
        $this->name = $method->name;
        $this->class = $method->class;
        $parameters = $this->method->getParameters();
        $types = [];
        if (!empty($parameters)) {
            foreach ($parameters as $param) {
                $type = $param->getType();
                if ($type != null) {
                    $types[] = $type->getName();
                } else {
                    $types[] = "undefined";
                }
            }
        }
        $this->parameterTypes = $types;
    }

    public function serialize()
    {
        return json_encode([
            'name' => $this->name,
            'class' => $this->class,
            'parameterTypes' => $this->parameterTypes
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->name = $json->name;
        $this->class = $json->class;
        $this->parameterTypes = $json->parameterTypes;

        $class = $class = new \ReflectionClass($this->class);
        $methods = $class->getMethods();
        foreach ($methods as $method) {
            if ($method->name == $this->name) {
                $this->method = $method;
                break;
            }
        }
    }
}
