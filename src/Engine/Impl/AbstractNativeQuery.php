<?php

namespace Jabe\Engine\Impl;

use Jabe\Engine\ProcessEngineException;
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext,
    CommandExecutorInterface
};
use Jabe\Engine\Query\NativeQueryInterface;

abstract class AbstractNativeQuery implements CommandInterface, NativeQueryInterface
{
    private const RESULT_TYPES = [
        'LIST' => 'LIST', 'LIST_PAGE' => 'LIST_PAGE', 'SINGLE_RESULT' => 'SINGLE_RESULT', 'COUNT' => 'COUNT'
    ];

    protected $commandExecutor;
    protected $commandContext;

    protected $maxResults = PHP_INT_MAX;
    protected $firstResult = 0;
    protected $resultType;

    private $parameters = [];
    private $sqlStatement;

    protected function __construct($command)
    {
        if ($command instanceof CommandExecutorInterface) {
            $this->commandExecutor = $commandExecutor;
        } elseif ($command instanceof CommandContext) {
            $this->commandContext = $commandContext;
        }
    }

    public function setCommandExecutor(CommandExecutorInterface $commandExecutor): AbstractNativeQuery
    {
        $this->commandExecutor = $commandExecutor;
        return $this;
    }

    public function sql(string $sqlStatement): NativeQueryInterface
    {
        $this->sqlStatement = $sqlStatement;
        return $this;
    }

    public function parameter(string $name, $value): NativeQueryInterface
    {
        $this->parameters[$name] = $value;
        return $this;
    }

    public function singleResult()
    {
        $this->resultType = self::RESULT_TYPES['SINGLE_RESULT'];
        if ($this->commandExecutor !== null) {
            return $this->commandExecutor->execute($this);
        }
        return $this->executeSingleResult(Context::getCommandContext());
    }

    public function list(): array
    {
        $this->resultType = self::RESULT_TYPES['LIST'];
        if ($this->commandExecutor !== null) {
            return $this->commandExecutor->execute($this);
        }
        return $this->executeList(Context::getCommandContext(), $this->getParameterMap(), 0, PHP_INT_MAX);
    }

    public function listPage(int $firstResult, int $maxResults): array
    {
        $this->firstResult = $firstResult;
        $this->maxResults = $maxResults;
        $this->resultType = self::RESULT_TYPES['LIST_PAGE'];
        if ($this->commandExecutor !== null) {
            return $this->commandExecutor->execute($this);
        }
        return $this->executeList(Context::getCommandContext(), $this->getParameterMap(), $this->firstResult, $this->maxResults);
    }

    public function count(): int
    {
        $this->resultType = self::RESULT_TYPES['COUNT'];
        if ($this->commandExecutor !== null) {
            return $this->commandExecutor->execute($this);
        }
        return $this->executeCount(Context::getCommandContext(), $this->getParameterMap());
    }

    public function execute(CommandContext $commandContext)
    {
        if ($this->resultType == self::RESULT_TYPES['LIST']) {
            return $this->executeList($commandContext, $this->getParameterMap(), 0, PHP_INT_MAX);
        } elseif ($this->resultType == self::RESULT_TYPES['LIST_PAGE']) {
            $parameterMap = $this->getParameterMap();
            $parameterMap["resultType"] = "LIST_PAGE";
            $parameterMap["firstResult"] = $this->firstResult;
            $parameterMap["maxResults"] = $this->maxResults;
            $parameterMap["internalOrderBy"] = "RES.ID_ asc";

            $firstRow = $this->firstResult + 1;
            $parameterMap["firstRow"] = $firstRow;
            $lastRow = 0;
            if ($maxResults == PHP_INT_MAX) {
                $lastRow = $maxResults;
            } else {
                $lastRow = $this->firstResult + $maxResults + 1;
            }
            $parameterMap["lastRow"] = $lastRow;
            return $this->executeList($commandContext, $parameterMap, $firstResult, $maxResults);
        } elseif ($resultType == self::RESULT_TYPES['SINGLE_RESULT']) {
            return $this->executeSingleResult($commandContext);
        } else {
            return $this->executeCount($commandContext, $this->getParameterMap());
        }
    }

    abstract public function executeCount(CommandContext $commandContext, array $parameterMap): int;

    /**
     * Executes the actual query to retrieve the list of results.
     * @param maxResults
     * @param firstResult
     *
     * @param page
     *          used if the results must be paged. If null, no paging will be
     *          applied.
     */
    abstract public function executeList(CommandContext $commandContext, array $parameterMap, int $firstResult, int $maxResults): array;

    public function executeSingleResult(CommandContext $commandContext)
    {
        $results = $this->executeList($commandContext, $this->getParameterMap(), 0, PHP_INT_MAX);
        if (count($results) == 1) {
            return $results[0];
        } elseif (count($results) > 1) {
            throw new ProcessEngineException("Query return " . count($results) . " results instead of max 1");
        }
        return null;
    }

    private function getParameterMap(): array
    {
        $parameterMap = $this->parameters;
        $parameterMap["sql"] = $this->sqlStatement;
        return $parameterMap;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }
}
