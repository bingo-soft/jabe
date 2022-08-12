<?php

namespace Jabe\Impl\Core\Variable\Scope;

class VariableCollectionProvider implements VariablesProviderInterface
{
    protected $variables = [];

    public function __construct(?array $variables = [])
    {
        $this->variables = $variables;
    }

    public function provideVariables(?array $variablesNames = []): array
    {
        if (vempty($variablesNames)) {
            return $this->variables;
        }

        $result = [];
        if (!empty($this->variables)) {
            foreach ($this->variables as $variable) {
                if (in_array($variable->getName(), $variablesNames)) {
                    $result[] = $variable;
                }
            }
        }
        return $result;
    }

    public static function emptyVariables(): VariableCollectionProvider
    {
        return new VariableCollectionProvider();
    }
}
