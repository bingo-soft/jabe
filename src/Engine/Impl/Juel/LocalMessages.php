<?php

namespace Jabe\Engine\Impl\Juel;

class LocalMessages
{
    public static $messages = [
        "message.unknown" => "Unknown message",
        "error.identifier.property.notfound" => "Cannot resolve identifier '%s'",
        "error.identifier.method.notfound" => "Cannot find method expression for identifier '%s' (null)",
        "error.identifier.method.notamethod" => "Cannot find method expression for identifier '%s' (found %s instead)",
        "error.identifier.method.access" => "Cannot access method '%s'",
        "error.identifier.method.invocation" => "Error invoking method '%s': %s",
        "error.property.base.null" => "Target unreachable, base expression '%s' resolved to null",
        "error.property.property.notfound" => "Cannot resolve property '%s' in '%s'",
        "error.property.method.notfound" => "Cannot find method '%s' in '%s'",
        "error.property.method.resolve" => "Cannot resolve method '%s' in '%s'",
        "error.property.method.access" => "Cannot access method '%s' in '%s'",
        "error.property.method.invocation" => "Error invoking method '%s' in '%s'",
        "error.function.invocation" => "Error invoking function '%s'",
        "error.function.access" => "Cannot access function '%s'",
        "error.function.nomapper" => "Expression uses functions, but no function mapper was provided",
        "error.function.notfound" => "Could not resolve function '%s'",
        "error.function.params" => "Parameters for function '%s' do not match",
        "error.method.literal.void" => "Expected type ''void'' is not allowed for literal method expression '%s'",
        "error.method.invalid" => "Expression '%s' is not a valid method expression",
        "error.method.notypes" => "Parameter types must not be null",
        "error.value.set.rvalue" => "Cannot set value of a non-lvalue expression '%s'",
        "error.value.notype" => "Expected type must not be null",
        "error.compare.types" => "Cannot compare '%s' and '%s'",
        "error.coerce.type" => "Cannot coerce from %s to %s",
        "error.coerce.value" => "Cannot coerce '%s' to %s",
        "error.negate" => "Cannot negate '%s'",
        "error.null" => "Expression cannot be null",
        "error.scan" => "lexical error at position %s, encountered %s, expected %s",
        "error.parse" => "syntax error at position %s, encountered %s, expected %s",
        "error.build" => "Error parsing '%s': %s",
        "error.config.builder" => "Error creating builder: %s"
    ];

    public static function get(string $key, ...$args): string
    {
        if (array_key_exists($key, self::$messages)) {
            return sprintf(self::$messages[$key], ...$args);
        }
        return self::$messages["message.unknown"];
    }
}
