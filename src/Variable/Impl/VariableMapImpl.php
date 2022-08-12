<?php

namespace Jabe\Variable\Impl;

use Jabe\Variable\Variables;
use Jabe\Variable\VariableMapInterface;
use Jabe\Variable\Context\VariableContextInterface;
use Jabe\Variable\Value\TypedValueInterface;

class VariableMapImpl implements VariableMapInterface, \Serializable, VariableContextInterface
{
    protected $variables = [];

    public function __construct($map = null)
    {
        if ($map instanceof VariableMapInterface) {
            $this->variables = $map->variables;
        } elseif (is_array($map)) {
            $this->putAll($map);
        }
    }

    public function putValue(string $name, $value): VariableMapInterface
    {
        $this->put($name, $value);
        return $this;
    }

    public function putValueTyped(string $name, TypedValueInterface $value): VariableMapInterface
    {
        $this->variables[$name] = $value;
        return $this;
    }

    public function getValue(string $name, string $type)
    {
        $object = $this->get($name);
        if ($object === null) {
            return null;
        } elseif (is_a($object, $type)) {
            return $object;
        } else {
            throw new \Exception("Cannot cast variable named '" . $name . "' with value '"  . $object . "' to type '" . $type . "'.");
        }
    }

    public function getValueTyped(string $name): ?TypedValueInterface
    {
        if (array_key_exists($name, $this->variables)) {
            return $this->variables[$name];
        }
        return null;
    }

    public function size(): int
    {
        return count($this->variables);
    }

    public function isEmpty(): bool
    {
        return empty($this->variables);
    }

    public function containsKey($key): bool
    {
        return array_key_exists($key, $this->variables);
    }

    public function containsValue($value): bool
    {
        foreach ($this->variables as $varValue) {
            if ($value == $varValue->getValue()) {
                return true;
            } elseif ($value !== null && method_exists($value, 'equals') && $value->equals($varValue->getValue())) {
                return true;
            }
        }
        return false;
    }

    public function get($key)
    {
        $typedValue = $this->variables[$key];

        if ($typedValue !== null) {
            return $typedValue->getValue();
        }
        return null;
    }

    public function put(string $key, $value)
    {
        $typedValue = Variables::untypedValue($value);

        $prevValue = null;
        if (array_key_exists($key, $this->variables)) {
            $prevValue = $this->variables[$key];
        }
        $this->variables[$key] = $typedValue;

        if ($prevValue !== null) {
            return $prevValue->getValue();
        }
        return null;
    }

    public function remove($key)
    {
        $prevValue = null;
        if (array_key_exists($key, $this->variables)) {
            $prevValue = $this->variables[$key];
            unset($this->variables[$key]);
        }

        if ($prevValue !== null) {
            return $prevValue->getValue();
        }
        return null;
    }

    public function putAll(array $m)
    {
        if ($m !== null) {
            if ($m instanceof VariableMapImpl) {
                $this->variables = array_merge($this->variables, $m->variables);
            } else {
                foreach ($m as $key => $value) {
                    $this->put($key, $value);
                }
            }
        }
    }

    public function clear(): void
    {
        $this->variables = [];
    }

    public function keySet(): array
    {
        return array_keys($this->variables);
    }

    public function __toString(): string
    {
        $stringBuilder = "";
        $stringBuilder .= "{\n";
        foreach ($this->variables as $key => $value) {
            $stringBuilder .= "  ";
            $stringBuilder .= $key;
            $stringBuilder .= " => ";
            $stringBuilder .= $value;
            $stringBuilder .= "\n";
        }
        $stringBuilder .= "}";
        return $stringBuilder;
    }

    public function serialize()
    {
        return json_encode($this->variables, true);
    }

    public function unserialize($data)
    {
        $this->variables = json_decode($data, true);
    }

    public function asValueMap(): array
    {
        $ret = [];
        foreach ($this->variables as $key => $value) {
            $ret[$key] = $value->getValue();
        }
        return $ret;
    }

    public function equals($other): bool
    {
        if ($other instanceof VariableMapInterface) {
            return $this->asValueMap() == $other->asValueMap();
        } elseif (is_array($other)) {
            return $this->asValueMap() == $other;
        }
        return false;
    }

    public function values(): array
    {
        return array_values($this->asValueMap());
    }

    public function resolve(string $variableName): ?TypedValueInterface
    {
        return $this->getValueTyped($variableName);
    }

    public function containsVariable(string $variableName): bool
    {
        return $this->containsKey($variableName);
    }

    public function asVariableContext(): VariableContextInterface
    {
        return $this;
    }
}
