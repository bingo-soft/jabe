<?php

namespace BpmPlatform\Engine\Impl\Core\Variable\Scope;

use BpmPlatform\Engine\Impl\Core\Variable\CoreVariableInstanceInterface;

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
            foreach ($variables as $variable) {
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
