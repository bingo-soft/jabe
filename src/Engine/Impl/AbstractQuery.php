<?php

namespace Jabe\Engine\Impl;

use Jabe\Engine\ProcessEngineException;
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Db\ListQueryParameterObject;
use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext,
    CommandExecutorInterface
};
use Jabe\Engine\Impl\Util\{
    EnsureUtil,
    QueryMaxResultsLimitUtil
};
use Jabe\Engine\Query\{
    QueryInterface,
    QueryPropertyInterface
};

abstract class AbstractQuery extends ListQueryParameterObject implements CommandInterface, QueryInterface
{
    public const SORTORDER_ASC = "asc";
    public const SORTORDER_DESC = "desc";

    protected const RESULT_TYPES = [
        'LIST' => 'LIST', 'LIST_PAGE' => 'LIST_PAGE', 'LIST_IDS' => 'LIST_IDS', 'LIST_DEPLOYMENT_ID_MAPPINGS' => 'LIST_DEPLOYMENT_ID_MAPPINGS', 'SINGLE_RESULT' => 'SINGLE_RESULT', 'COUNT' => 'COUNT'
    ];

    protected $commandExecutor;

    protected $resultType;

    protected $expressions = [];

    protected $validators = [];

    protected $maxResultsLimitEnabled;

    public function __construct(CommandExecutorInterface $commandExecutor = null)
    {
        if ($commandExecutor !== null) {
            $this->commandExecutor = $commandExecutor;

            // all queries that are created with a dedicated command executor
            // are treated as adhoc queries (i.e. queries not created in the context
            // of a command)
            $this->addValidator(AdhocQueryValidator::instance());
        }
    }

    public function setCommandExecutor(CommandExecutorInterface $commandExecutor): AbstractQuery
    {
        $this->commandExecutor = $commandExecutor;
        return $this;
    }

    public function orderBy($property): QueryInterface
    {
        if ($property instanceof QueryPropertyInterface) {
            return $this->orderBy(new QueryOrderingProperty(null, $property));
        } else /*if ($property instanceof QueryOrderingProperty)*/{
            $this->orderingProperties[] = $property;
            return $this;
        }
    }

    public function asc(): QueryInterface
    {
        return $this->direction(Direction::ascending());
    }

    public function desc(): QueryInterface
    {
        return $this->direction(Direction::descending());
    }

    public function direction(Direction $direction): QueryInterface
    {
        $currentOrderingProperty = null;

        if (!empty($this->orderingProperties)) {
            $currentOrderingProperty = $this->orderingProperties[count($this->orderingProperties) - 1];
        }

        EnsureUtil::ensureNotNull("You should call any of the orderBy methods first before specifying a direction", "currentOrderingProperty", $currentOrderingProperty);

        if ($currentOrderingProperty->getDirection() !== null) {
            EnsureUtil::ensureNull("Invalid query: can specify only one direction desc() or asc() for an ordering constraint", "direction", $direction);
        }

        $currentOrderingProperty->setDirection($direction);
        return $this;
    }

    protected function checkQueryOk(): void
    {
        foreach ($this->orderingProperties as $orderingProperty) {
            EnsureUtil::ensureNotNull("Invalid query: call asc() or desc() after using orderByXX()", "direction", $orderingProperty->getDirection());
        }
    }

    public function singleResult()
    {
        $this->resultType = self::RESULT_TYPES['SINGLE_RESULT'];
        return $this->executeResult($this->resultType);
    }

    public function list(): array
    {
        $this->resultType = self::RESULT_TYPES['LIST'];
        return $this->executeResult($this->resultType);
    }

    public function listPage(int $firstResult, int $maxResults): array
    {
        $this->firstResult = $firstResult;
        $this->maxResults = $maxResults;
        $this->resultType = self::RESULT_TYPES['LIST_PAGE'];
        return $this->executeResult($this->resultType);
    }

    public function executeResult(string $resultType)
    {
        if ($this->commandExecutor !== null) {
            if (!$this->maxResultsLimitEnabled) {
                $this->maxResultsLimitEnabled = Context::getCommandContext() === null;
            }
            return $this->commandExecutor->execute($this);
        }

        switch ($resultType) {
            case self::RESULT_TYPES['SINGLE_RESULT']:
                return $this->executeSingleResult(Context::getCommandContext());
            case self::RESULT_TYPES['LIST_PAGE']:
            case self::RESULT_TYPES['LIST']:
                return $this->evaluateExpressionsAndExecuteList(Context::getCommandContext(), null);
            default:
                throw new ProcessEngineException("Unknown result type!");
        }
    }

    public function count(): int
    {
        $this->resultType = self::RESULT_TYPES['COUNT'];
        if ($this->commandExecutor !== null) {
            return $this->commandExecutor->execute($this);
        }
        return $this->evaluateExpressionsAndExecuteCount(Context::getCommandContext());
    }

    public function unlimitedList(): array
    {
        $this->resultType = self::RESULT_TYPES['LIST'];
        if ($this->commandExecutor !== null) {
            return $this->commandExecutor->execute($this);
        }
        return $this->evaluateExpressionsAndExecuteList(Context::getCommandContext(), null);
    }

    public function execute(CommandContext $commandContext)
    {
        if ($this->resultType == self::RESULT_TYPES['LIST']) {
            return $this->evaluateExpressionsAndExecuteList($commandContext, null);
        } elseif ($this->resultType == self::RESULT_TYPES['SINGLE_RESULT']) {
            return $this->executeSingleResult($commandContext);
        } elseif ($this->resultType == self::RESULT_TYPES['LIST_PAGE']) {
            return $this->evaluateExpressionsAndExecuteList($commandContext, null);
        } elseif ($this->resultType == self::RESULT_TYPES['LIST_IDS']) {
            return $this->evaluateExpressionsAndExecuteIdsList($commandContext);
        } elseif ($this->resultType == self::RESULT_TYPES['LIST_DEPLOYMENT_ID_MAPPINGS']) {
            return $this->evaluateExpressionsAndExecuteDeploymentIdMappingsList($commandContext);
        } else {
            return $this->evaluateExpressionsAndExecuteCount($commandContext);
        }
    }

    public function evaluateExpressionsAndExecuteCount(CommandContext $commandContext): int
    {
        $this->validate();
        $this->evaluateExpressions();
        return !$this->hasExcludingConditions() ? $this->executeCount($commandContext) : 0;
    }

    abstract public function executeCount(CommandContext $commandContext): int;

    public function evaluateExpressionsAndExecuteList(CommandContext $commandContext, Page $page): array
    {
        $this->checkMaxResultsLimit();
        $this->validate();
        $this->evaluateExpressions();
        return !$this->hasExcludingConditions() ? $this->executeList($commandContext, $page) : [];
    }

    /**
     * Whether or not the query has excluding conditions. If the query has excluding conditions,
     * (e.g. task due date before and after are excluding), the SQL query is avoided and a default result is
     * returned. The returned result is the same as if the SQL was executed and there were no entries.
     *
     * @return {@code true} if the query does have excluding conditions, {@code false} otherwise
     */
    protected function hasExcludingConditions(): bool
    {
        return false;
    }

    /**
     * Executes the actual query to retrieve the list of results.
     * @param page used if the results must be paged. If null, no paging will be applied.
     */
    abstract public function executeList(CommandContext $commandContext, Page $page): array;

    public function executeSingleResult(CommandContext $commandContext)
    {
        $this->disableMaxResultsLimit();
        $results = $this->evaluateExpressionsAndExecuteList($commandContext, new Page(0, 2));
        if (count($results) == 1) {
            return $results[0];
        } elseif (count($results) > 1) {
            throw new ProcessEngineException("Query return " . count($results) . " results instead of max 1");
        }
        return null;
    }

    public function getExpressions(): array
    {
        return $this->expressions;
    }

    public function setExpressions(array $expressions): void
    {
        $this->expressions = $expressions;
    }

    public function addExpression(string $key, string $expression): void
    {
        $this->expressions[$key] = $expression;
    }

    protected function evaluateExpressions(): void
    {
        // we cannot iterate directly on the entry set cause the expressions
        // are removed by the setter methods during the iteration
        $entries = $this->expressions;

        foreach ($entries as $methodName => $expression) {
            $value = null;

            try {
                $value = Context::getProcessEngineConfiguration()
                    ->getExpressionManager()
                    ->createExpression($expression)
                    ->getValue(null);
            } catch (ProcessEngineException $e) {
                throw new ProcessEngineException("Unable to resolve expression '" . $expression . "' for method '" . $methodName . "' on class '" . get_class($this) . "'", $e);
            }

            // automatically convert DateTime to date
            if ($value instanceof \DateTime) {
                $value = $value->format('c');
            }

            try {
                $method = $this->getMethod($methodName);
                $method->invoke($this, $value);
            } catch (\Exception $e) {
                throw new ProcessEngineException("Unable to access method '" . $methodName . "' on class '" . get_class($this) . "'", $e);
            }
        }
    }

    protected function getMethod(string $methodName): \ReflectionMethod
    {
        $ref = new \ReflectionClass($this);
        foreach ($ref->getMethods() as $method) {
            if ($method->name == $methodName) {
                return $method;
            }
        }
        throw new ProcessEngineException("Unable to find method '" . $methodName . "' on class '"  . get_class($this) .  "'");
    }

    public function extend(QueryInterface $extendingQuery)
    {
        throw new ProcessEngineException("Extending of query type '" . get_class($extendingQuery) . "' currently not supported");
    }

    protected function mergeOrdering(AbstractQuery $extendedQuery, AbstractQuery $extendingQuery): void
    {
        $extendedQuery->orderingProperties = $this->orderingProperties;
        if (!empty($extendingQuery->orderingProperties)) {
            if (empty($extendedQuery->orderingProperties)) {
                $extendedQuery->orderingProperties = $extendingQuery->orderingProperties;
            } else {
                $extendedQuery->orderingProperties = array_merge($extendedQuery->orderingProperties, $extendingQuery->orderingProperties);
            }
        }
    }

    protected function mergeExpressions(AbstractQuery $extendedQuery, AbstractQuery $extendingQuery): void
    {
        $mergedExpressions = $extendingQuery->getExpressions();
        foreach ($this->getExpressions() as $key => $value) {
            if (!array_key_exists($key, $mergedExpressions)) {
                $mergedExpressions[$key] = $value;
            }
        }
        $extendedQuery->setExpressions($mergedExpressions);
    }

    public function validate(ValidatorInterface $validator = null): void
    {
        if ($validator !== null) {
            $validator->validate($this);
        } else {
            foreach ($this->validators as $validator) {
                $this->validate($validator);
            }
        }
    }

    public function addValidator(ValidatorInterface $validator): void
    {
        $this->validators[] = $validator;
    }

    public function removeValidator(ValidatorInterface $validator): void
    {
        foreach ($this->validators as $key => $curValidator) {
            if ($curValidator == $validator) {
                unset($this->validators[$key]);
            }
        }
    }

    public function listIds(): array
    {
        $this->resultType = self::RESULT_TYPES['LIST_IDS'];
        $ids = [];
        if ($this->commandExecutor !== null) {
            $ids = $this->commandExecutor->execute($this);
        } else {
            $ids = $this->evaluateExpressionsAndExecuteIdsList(Context::getCommandContext());
        }

        if (!empty($ids)) {
            QueryMaxResultsLimitUtil::checkMaxResultsLimit(count($ids));
        }

        return $ids;
    }

    public function listDeploymentIdMappings(): array
    {
        $this->resultType = self::RESULT_TYPES['LIST_DEPLOYMENT_ID_MAPPINGS'];
        $ids = [];
        if ($this->commandExecutor !== null) {
            $ids = $this->commandExecutor->execute($this);
        } else {
            $ids = $this->evaluateExpressionsAndExecuteDeploymentIdMappingsList(Context::getCommandContext());
        }

        if (!empty($ids)) {
            QueryMaxResultsLimitUtil::checkMaxResultsLimit(count($ids));
        }

        return $ids;
    }

    public function evaluateExpressionsAndExecuteIdsList(CommandContext $commandContext): array
    {
        $this->validate();
        $this->evaluateExpressions();
        return !$this->hasExcludingConditions() ? $this->executeIdsList($commandContext) : [];
    }

    public function executeIdsList(CommandContext $commandContext): array
    {
        throw new UnsupportedOperationException("executeIdsList not supported by " . get_class($this));
    }

    public function evaluateExpressionsAndExecuteDeploymentIdMappingsList(CommandContext $commandContext): array
    {
        $this->validate();
        $this->evaluateExpressions();
        return !$this->hasExcludingConditions() ? $this->executeDeploymentIdMappingsList($commandContext) : [];
    }

    public function executeDeploymentIdMappingsList(CommandContext $commandContext): array
    {
        throw new UnsupportedOperationException("executeDeploymentIdMappingsList not supported by " . get_class($this));
    }

    protected function checkMaxResultsLimit(): void
    {
        if ($this->maxResultsLimitEnabled) {
            QueryMaxResultsLimitUtil::checkMaxResultsLimit($this->maxResults);
        }
    }

    public function enableMaxResultsLimit(): void
    {
        $this->maxResultsLimitEnabled = true;
    }

    public function disableMaxResultsLimit(): void
    {
        $this->maxResultsLimitEnabled = false;
    }
}
