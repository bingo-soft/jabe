<?php

namespace Jabe\Impl\Persistence;

use Jabe\Impl\ProcessEngineLogger;
use Jabe\Impl\Context\Context;
use Jabe\Impl\Db\EnginePersistenceLogger;
use Jabe\Impl\History\HistoryLevel;

class AbstractHistoricManager extends AbstractManager
{
    //protected static final EnginePersistenceLogger LOG = ProcessEngineLogger.PERSISTENCE_LOGGER;

    protected $historyLevel;// = Context.getProcessEngineConfiguration().getHistoryLevel();

    protected $isHistoryEnabled;// = !historyLevel.equals(HistoryLevel.HISTORY_LEVEL_NONE);
    protected $isHistoryLevelFullEnabled;// = historyLevel.equals(HistoryLevel.HISTORY_LEVEL_FULL);

    public function __construct(...$args)
    {
        parent::__construct(...$args);
        $this->historyLevel = Context::getProcessEngineConfiguration()->getHistoryLevel();
        $this->isHistoryEnabled = $this->historyLevel != HistoryLevel::historyLevelNone();
        $this->isHistoryLevelFullEnabled = $this->historyLevel == HistoryLevel::historyLevelFull();
    }

    protected function checkHistoryEnabled(): void
    {
        if (!$this->isHistoryEnabled) {
            //throw LOG.disabledHistoryException();
            throw new \Exception("disabledHistoryException");
        }
    }

    public function isHistoryEnabled(): bool
    {
        return $this->isHistoryEnabled;
    }

    public function isHistoryLevelFullEnabled(): bool
    {
        return $this->isHistoryLevelFullEnabled;
    }
}
