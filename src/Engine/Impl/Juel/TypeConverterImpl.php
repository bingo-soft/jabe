<?php

namespace BpmPlatform\Engine\Impl\Juel;

use BpmPlatform\Engine\Impl\Util\El\ELException;

class TypeConverterImpl extends TypeConverter
{
    private function throwException($value, string $shouldBe)
    {
        $type = gettype($value);
        if ($type == "object") {
            $type .= ":" . get_class($value);
        }
        throw new ELException(LocalMessages::get("error.coerce.type", $type, $shouldBe));
    }

    protected function coerceToBoolean($value): bool
    {
        if ($value == null || $value == "") {
            return false;
        }
        if (gettype($value)  == "boolean") {
            return $value;
        }
        if (gettype($value)  == "string") {
            return boolval($value);
        }
        $this->throwException($value, "boolean");
    }

    protected function coerceToCharacter($value): string
    {
        if ($value == null || $value == "") {
            return "";
        }
        if (gettype($value)  == "string") {
            return strlen($value) >= 1 ? $value[0] : "";
        }
        if (is_numeric($value)) {
            return strval($value);
        }
        $this->throwException($value, "character");
    }

    protected function coerceToDouble($value, string $shouldBe = "double"): float
    {
        if ($value == null || $value == "") {
            return 0.0;
        }
        if (gettype($value)  == "double") {
            return $value;
        }
        if (is_numeric($value)) {
            return floatval($value);
        }
        $this->throwException($value, $shouldBe);
    }

    protected function coerceToFloat($value): float
    {
        return $this->coerceToDouble($value, "float");
    }

    protected function coerceToInteger($value): int
    {
        if ($value == null || $value == "") {
            return 0;
        }
        if (gettype($value)  == "integer") {
            return $value;
        }
        if (is_numeric($value)) {
            return intval($value);
        }
        $this->throwException($value, "integer");
    }

    protected function coerceToString($value): string
    {
        if ($value == null) {
            return "";
        }
        try {
            return strval($value);
        } catch (\Exception $e) {
            $this->throwException($value, "string");
        }
    }

    protected function coerceStringToType(string $value, string $type)
    {
        return $this->coerceToType($value, $type);
    }

    protected function coerceToType($value, string $type)
    {
        switch ($type) {
            case "boolean":
            case "bool":
                return $this->coerceToBoolean($value);
            case "double":
            case "float":
                return $this->coerceToDouble($value);
            case "integer":
            case "int":
                return $this->coerceToInteger($value);
            case "string":
                return $this->coerceToString($value);
        }
        if (gettype($value) == "object" && get_class($value) == $type) {
            return $value;
        }
        if (is_array($value) && $type == "array") {
            return $value;
        }
        $this->throwException($value, $type);
    }

    public function convert($value, string $type)
    {
        return $this->coerceToType($value, $type);
    }
}
