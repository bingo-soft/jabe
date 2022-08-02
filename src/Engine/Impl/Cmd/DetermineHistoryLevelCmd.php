<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\ProcessEngineException;
use Jabe\Engine\Impl\HistoryLevelSetupCommand;
use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class DetermineHistoryLevelCmd implements CommandInterface
{
    private $historyLevels = [];

    public function __construct(array $historyLevels)
    {
        $this->historyLevels = $historyLevels;
    }

    public function execute(CommandContext $commandContext)
    {
        $databaseHistoryLevel = HistoryLevelSetupCommand::databaseHistoryLevel($commandContext);

        $result = null;

        if ($databaseHistoryLevel !== null) {
            foreach ($this->historyLevels as $historyLevel) {
                if ($historyLevel->getId() == $databaseHistoryLevel) {
                    $result = $historyLevel;
                    break;
                }
            }
            if ($result !== null) {
                return $result;
            } else {
                // if a custom non-null value is not registered, throw an exception.
                throw new ProcessEngineException(sprintf("The configured history level with id='%s' is not registered in this config.", $databaseHistoryLevel));
            }
        } else {
            return null;
        }
    }
}
