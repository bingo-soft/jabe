<?php

namespace Jabe\Impl;

use Jabe\Exception\NotValidException;
use Jabe\Impl\Cfg\ProcessEngineConfigurationImpl;
use Jabe\Impl\Context\Context;
use Jabe\Impl\Interceptor\{
    CommandContext,
    CommandExecutorInterface
};
use Jabe\Impl\Util\EnsureUtil;
use Jabe\Query\QueryInterface;

abstract class AbstractVariableQueryImpl extends AbstractQuery
{
    protected $queryVariableValues = [];

    protected $variableNamesIgnoreCase;
    protected $variableValuesIgnoreCase;

    public function __construct(CommandExecutorInterface $commandExecutor = null)
    {
        if ($commandExecutor !== null) {
            parent::__construct($commandExecutor);
        }
    }

    abstract public function executeCount(CommandContext $commandContext): int;

    abstract public function executeList(CommandContext $commandContext, Page $page): array;

    public function variableValueEquals(string $name, $value): QueryInterface
    {
        $this->addVariable($name, $value, QueryOperator::EQUALS, true);
        return $this;
    }

    public function variableValueNotEquals(string $name, $value): QueryInterface
    {
        $this->addVariable($name, $value, QueryOperator::NOT_EQUALS, true);
        return $this;
    }

    public function variableValueGreaterThan(string $name, $value): QueryInterface
    {
        $this->addVariable($name, $value, QueryOperator::GREATER_THAN, true);
        return $this;
    }

    public function variableValueGreaterThanOrEqual(string $name, $value): QueryInterface
    {
        $this->addVariable($name, $value, QueryOperator::GREATER_THAN_OR_EQUAL, true);
        return $this;
    }

    public function variableValueLessThan(string $name, $value): QueryInterface
    {
        $this->addVariable($name, $value, QueryOperator::LESS_THAN, true);
        return $this;
    }

    public function variableValueLessThanOrEqual(string $name, $value): QueryInterface
    {
        $this->addVariable($name, $value, QueryOperator::LESS_THAN_OR_EQUAL, true);
        return $this;
    }

    public function variableValueLike(string $name, string $value): QueryInterface
    {
        $this->addVariable($name, $value, QueryOperator::LIKE, true);
        return $this;
    }

    public function variableValueNotLike(string $name, string $value): QueryInterface
    {
        $this->addVariable($name, $value, QueryOperator::NOT_LIKE, true);
        return $this;
    }

    public function matchVariableNamesIgnoreCase(): QueryInterface
    {
        $this->variableNamesIgnoreCase = true;
        foreach ($this->getQueryVariableValues() as $variable) {
            $variable->setVariableNameIgnoreCase(true);
        }
        return $this;
    }

    public function matchVariableValuesIgnoreCase(): QUeryInterface
    {
        $this->variableValuesIgnoreCase = true;
        foreach ($this->getQueryVariableValues() as $variable) {
            $variable->setVariableValueIgnoreCase(true);
        }
        return $this;
    }

    protected function addVariable(string $name, $value, string $operator, bool $processInstanceScope): void
    {
        $queryVariableValue = $this->createQueryVariableValue($name, $value, $operator, $processInstanceScope);
        $this->queryVariableValues[] = $queryVariableValue;
    }

    protected function createQueryVariableValue(string $name, $value, string $operator, bool $processInstanceScope): QueryVariableValue
    {
        $this->validateVariable($name, $value, $operator);

        $shouldMatchVariableValuesIgnoreCase = $this->variableValuesIgnoreCase == true && $value !== null && is_string($value);
        $shouldMatchVariableNamesIgnoreCase = $this->variableNamesIgnoreCase == true;

        return new QueryVariableValue($name, $value, $operator, $processInstanceScope, $shouldMatchVariableNamesIgnoreCase, $shouldMatchVariableValuesIgnoreCase);
    }

    protected function validateVariable(string $name, $value, string $operator): void
    {
        EnsureUtil::ensureNotNull(NotValidException::class, "name", $name);
        if ($value === null || $this->isBoolean($value)) {
            // Null-values and booleans can only be used in EQUALS and NOT_EQUALS
            switch ($operator) {
                case QueryOperator::GREATER_THAN:
                    throw new NotValidException("Booleans and null cannot be used in 'greater than' condition");
                case QueryOperator::LESS_THAN:
                    throw new NotValidException("Booleans and null cannot be used in 'less than' condition");
                case QueryOperator::GREATER_THAN_OR_EQUAL:
                    throw new NotValidException("Booleans and null cannot be used in 'greater than or equal' condition");
                case QueryOperator::LESS_THAN_OR_EQUAL:
                    throw new NotValidException("Booleans and null cannot be used in 'less than or equal' condition");
                case QueryOperator::LIKE:
                    throw new NotValidException("Booleans and null cannot be used in 'like' condition");
                case QueryOperator::NOT_LIKE:
                    throw new NotValidException("Booleans and null cannot be used in 'not like' condition");
                default:
                    break;
            }
        }
    }

    private function isBoolean($value = null): bool
    {
        if ($value === null) {
            return false;
        }
        return is_bool($value);
    }

    protected function ensureVariablesInitialized(): void
    {
        if (!empty($this->getQueryVariableValues())) {
            $processEngineConfiguration = Context::getProcessEngineConfiguration();
            $variableSerializers = $processEngineConfiguration->getVariableSerializers();
            $dbType = $processEngineConfiguration->getDatabaseType();
            foreach ($this->getQueryVariableValues() as $queryVariableValue) {
                $queryVariableValue->initialize($variableSerializers, $dbType);
            }
        }
    }

    public function getQueryVariableValues(): array
    {
        return $this->queryVariableValues;
    }

    public function isVariableNamesIgnoreCase(): bool
    {
        return $this->variableNamesIgnoreCase;
    }

    public function isVariableValuesIgnoreCase(): bool
    {
        return $this->variableValuesIgnoreCase;
    }
}
