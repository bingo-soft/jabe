<?php

namespace Jabe\Impl\History;

use Jabe\Impl\Event\HistoryEventTypeInterface;

abstract class AbstractHistoryLevel implements HistoryLevelInterface
{
    private static $HISTORY_LEVEL_NONE;
    private static $HISTORY_LEVEL_ACTIVITY;
    private static $HISTORY_LEVEL_AUDIT;
    private static $HISTORY_LEVEL_FULL;

    public static function none(): AbstractHistoryLevel
    {
        if (self::$HISTORY_LEVEL_NONE === null) {
            self::$HISTORY_LEVEL_NONE = new HistoryLevelNone();
        }
        return self::$HISTORY_LEVEL_NONE;
    }

    public static function activity(): AbstractHistoryLevel
    {
        if (self::$HISTORY_LEVEL_ACTIVITY === null) {
            self::$HISTORY_LEVEL_ACTIVITY = new HistoryLevelActivity();
        }
        return self::$HISTORY_LEVEL_ACTIVITY;
    }

    public static function audit(): AbstractHistoryLevel
    {
        if (self::$HISTORY_LEVEL_AUDIT === null) {
            self::$HISTORY_LEVEL_AUDIT = new HistoryLevelAudit();
        }
        return self::$HISTORY_LEVEL_AUDIT;
    }

    public static function full(): AbstractHistoryLevel
    {
        if (self::$HISTORY_LEVEL_FULL === null) {
            self::$HISTORY_LEVEL_FULL = new HistoryLevelFull();
        }
        return self::$HISTORY_LEVEL_FULL;
    }

    /** An unique id identifying the history level.
     * The id is used internally to uniquely identify the history level and also stored in the database.
     */
    abstract public function getId(): int;

    /** An unique name identifying the history level.
     * The name of the history level can be used when configuring the process engine.
     * @see ProcessEngineConfiguration#setHistory(String)
     */
    abstract public function getName(): string;

    /**
     * Returns true if a given history event should be produced.
     * @param eventType the type of the history event which is about to be produced
     * @param entity the runtime structure used to produce the history event. Examples ExecutionEntity,
     * TaskEntity, VariableInstanceEntity, ... If a 'null' value is provided, the implementation
     * should return true if events of this type should be produced "in general".
     */
    abstract public function isHistoryEventProduced(HistoryEventTypeInterface $eventType, $entity): bool;
}
