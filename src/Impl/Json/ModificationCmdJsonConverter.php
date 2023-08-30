<?php

namespace Jabe\Impl\Json;

use Jabe\Impl\Cmd\{
    AbstractProcessInstanceModificationCommand,
    ActivityAfterInstantiationCmd,
    ActivityBeforeInstantiationCmd,
    ActivityCancellationCmd,
    ActivityInstanceCancellationCmd,
    TransitionInstanceCancellationCmd,
    TransitionInstantiationCmd
};
use Jabe\Impl\Util\JsonUtil;

class ModificationCmdJsonConverter extends JsonObjectConverter
{
    private static $INSTANCE;
    public const START_BEFORE = "startBeforeActivity";
    public const START_AFTER = "startAfterActivity";
    public const START_TRANSITION = "startTransition";
    public const CANCEL_ALL = "cancelAllForActivity";
    public const CANCEL_CURRENT = "cancelCurrentActiveActivityInstances";
    public const CANCEL_ACTIVITY_INSTANCES = "cancelActivityInstances";
    public const PROCESS_INSTANCE = "processInstances";
    public const CANCEL_TRANSITION_INSTANCES = "cancelTransitionInstances";

    public static function instance(): ModificationCmdJsonConverter
    {
        if (self::$INSTANCE == null) {
            self::$INSTANCE = new ModificationCmdJsonConverter();
        }
        return self::$INSTANCE;
    }

    public function toJsonObject(/*AbstractProcessInstanceModificationCommand*/$command, bool $isOrQueryActive = false): ?\stdClass
    {
        $json = JsonUtil::createObject();

        if ($command instanceof ActivityAfterInstantiationCmd) {
            JsonUtil::addField($json, self::START_AFTER, $command->getTargetElementId());
        } elseif ($command instanceof ActivityBeforeInstantiationCmd) {
            JsonUtil::addField($json, self::START_BEFORE, $command->getTargetElementId());
        } elseif ($command instanceof TransitionInstantiationCmd) {
            JsonUtil::addField($json, self::START_TRANSITION, $command->getTargetElementId());
        } elseif ($command instanceof ActivityCancellationCmd) {
            JsonUtil::addField($json, self::CANCEL_ALL, $command->getActivityId());
            JsonUtil::addField($json, self::CANCEL_CURRENT, $command->isCancelCurrentActiveActivityInstances());
        } elseif ($command instanceof ActivityInstanceCancellationCmd) {
            JsonUtil::addField($json, self::CANCEL_ACTIVITY_INSTANCES, $command->getActivityInstanceId());
            JsonUtil::addField($json, self::PROCESS_INSTANCE, $command->getProcessInstanceId());
        } elseif ($command instanceof TransitionInstanceCancellationCmd) {
            JsonUtil::addField($json, self::CANCEL_TRANSITION_INSTANCES, $command->getTransitionInstanceId());
            JsonUtil::addField($json, self::PROCESS_INSTANCE, $command->getProcessInstanceId());
        }

        return $json;
    }

    public function toObject(\stdClass $json, bool $isOrQuery = false)
    {
        $cmd = null;

        if (property_exists($json, self::START_BEFORE)) {
            $cmd = new ActivityBeforeInstantiationCmd(JsonUtil::getString($json, self::START_BEFORE));
        } elseif (property_exists($json, self::START_AFTER)) {
            $cmd = new ActivityAfterInstantiationCmd(JsonUtil::getString($json, self::START_AFTER));
        } elseif (property_exists($json, self::START_TRANSITION)) {
            $cmd = new TransitionInstantiationCmd(JsonUtil::getString($json, self::START_TRANSITION));
        } elseif (property_exists($json, self::CANCEL_ALL)) {
            $cmd = new ActivityCancellationCmd(JsonUtil::getString($json, self::CANCEL_ALL));
            $cancelCurrentActiveActivityInstances = JsonUtil::getBoolean($json, self::CANCEL_CURRENT);
            $cmd->setCancelCurrentActiveActivityInstances($cancelCurrentActiveActivityInstances);
        } elseif (property_exists($json, self::CANCEL_ACTIVITY_INSTANCES)) {
            $cmd = new ActivityInstanceCancellationCmd(JsonUtil::getString($json, self::PROCESS_INSTANCE), JsonUtil::getString($json, self::CANCEL_ACTIVITY_INSTANCES));
        } elseif (property_exists($json, self::CANCEL_TRANSITION_INSTANCES)) {
            $cmd = new TransitionInstanceCancellationCmd(JsonUtil::getString($json, self::PROCESS_INSTANCE), JsonUtil::getString($json, self::CANCEL_TRANSITION_INSTANCES));
        }

        return $cmd;
    }
}
