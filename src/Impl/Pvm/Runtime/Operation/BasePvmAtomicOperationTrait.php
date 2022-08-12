<?php

namespace Jabe\Impl\Pvm\Runtime\Operation;

use Jabe\Impl\Pvm\Runtime\{
    AtomicOperationInterface,
    PvmExecutionImpl
};

trait BasePvmAtomicOperationTrait
{
    private static $PROCESS_START;
    private static $FIRE_PROCESS_START;
    private static $PROCESS_END;

    public static function processStart(): PvmAtomicOperationInterface
    {
        if (self::$PROCESS_START === null) {
            self::$PROCESS_START = new PvmAtomicOperationProcessStart();
        }
        return self::$PROCESS_START;
    }

    public static function fireProcessStart(): PvmAtomicOperationInterface
    {
        if (self::$FIRE_PROCESS_START === null) {
            self::$FIRE_PROCESS_START = new PvmAtomicOperationFireProcessStart();
        }
        return self::$FIRE_PROCESS_START;
    }

    public static function processEnd(): PvmAtomicOperationInterface
    {
        if (self::$PROCESS_END === null) {
            self::$PROCESS_END = new PvmAtomicOperationProcessEnd();
        }
        return self::$PROCESS_END;
    }

    private static $ACTIVITY_START;
    private static $ACTIVITY_START_CONCURRENT;
    private static $ACTIVITY_START_CANCEL_SCOPE;
    private static $ACTIVITY_START_INTERRUPT_SCOPE;
    private static $ACTIVITY_START_CREATE_SCOPE;
    private static $ACTIVITY_INIT_STACK_NOTIFY_LISTENER_START;
    private static $ACTIVITY_INIT_STACK_NOTIFY_LISTENER_RETURN;
    private static $ACTIVITY_INIT_STACK;
    private static $ACTIVITY_INIT_STACK_AND_RETURN;
    private static $ACTIVITY_EXECUTE;
    private static $ACTIVITY_NOTIFY_LISTENER_END;
    private static $ACTIVITY_END;
    private static $FIRE_ACTIVITY_END;

    public static function activityStart(): PvmAtomicOperationInterface
    {
        if (self::$ACTIVITY_START === null) {
            self::$ACTIVITY_START = new PvmAtomicOperationActivityStart();
        }
        return self::$ACTIVITY_START;
    }

    public static function activityStartConcurrent(): PvmAtomicOperationInterface
    {
        if (self::$ACTIVITY_START_CONCURRENT === null) {
            self::$ACTIVITY_START_CONCURRENT = new PvmAtomicOperationActivityStartConcurrent();
        }
        return self::$ACTIVITY_START_CONCURRENT;
    }

    public static function activityStartCancelScope(): PvmAtomicOperationInterface
    {
        if (self::$ACTIVITY_START_INTERRUPT_SCOPE === null) {
            self::$ACTIVITY_START_INTERRUPT_SCOPE = new PvmAtomicOperationActivityStartInterruptEventScope();
        }
        return self::$ACTIVITY_START_INTERRUPT_SCOPE;
    }

    public static function activityStartInterruptScope(): PvmAtomicOperationInterface
    {
        if (self::$ACTIVITY_START_CANCEL_SCOPE === null) {
            self::$ACTIVITY_START_CANCEL_SCOPE = new PvmAtomicOperationActivityStartCancelScope();
        }
        return self::$ACTIVITY_START_CANCEL_SCOPE;
    }

    public static function activityStartCreateScope(): PvmAtomicOperationInterface
    {
        if (self::$ACTIVITY_START_CREATE_SCOPE === null) {
            self::$ACTIVITY_START_CREATE_SCOPE = new PvmAtomicOperationActivityStartCreateScope();
        }
        return self::$ACTIVITY_START_CREATE_SCOPE;
    }

    public static function activityInitStackNotifyListenerStart(): PvmAtomicOperationInterface
    {
        if (self::$ACTIVITY_INIT_STACK_NOTIFY_LISTENER_START === null) {
            self::$ACTIVITY_INIT_STACK_NOTIFY_LISTENER_START = new PvmAtomicOperationActivityInitStackNotifyListenerStart();
        }
        return self::$ACTIVITY_INIT_STACK_NOTIFY_LISTENER_START;
    }

    public static function activityInitStackNotifyListenerReturn(): PvmAtomicOperationInterface
    {
        if (self::$ACTIVITY_INIT_STACK_NOTIFY_LISTENER_RETURN === null) {
            self::$ACTIVITY_INIT_STACK_NOTIFY_LISTENER_RETURN = new PvmAtomicOperationActivityInitStackNotifyListenerReturn();
        }
        return self::$ACTIVITY_INIT_STACK_NOTIFY_LISTENER_RETURN;
    }

    public static function activityInitStack(): PvmAtomicOperationInterface
    {
        if (self::$ACTIVITY_INIT_STACK === null) {
            self::$ACTIVITY_INIT_STACK = new PvmAtomicOperationActivityInitStack(self::activityInitStackNotifyListenerStart());
        }
        return self::$ACTIVITY_INIT_STACK;
    }

    public static function activityInitStackAndReturn(): PvmAtomicOperationInterface
    {
        if (self::$ACTIVITY_INIT_STACK_AND_RETURN === null) {
            self::$ACTIVITY_INIT_STACK_AND_RETURN = new PvmAtomicOperationActivityInitStack(self::activityInitStackNotifyListenerReturn());
        }
        return self::$ACTIVITY_INIT_STACK_AND_RETURN;
    }

    public static function activityExecute(): PvmAtomicOperationInterface
    {
        if (self::$ACTIVITY_EXECUTE === null) {
            self::$ACTIVITY_EXECUTE = new PvmAtomicOperationActivityExecute();
        }
        return self::$ACTIVITY_EXECUTE;
    }

    public static function activityNotifyListenerEnd(): PvmAtomicOperationInterface
    {
        if (self::$ACTIVITY_NOTIFY_LISTENER_END === null) {
            self::$ACTIVITY_NOTIFY_LISTENER_END = new PvmAtomicOperationActivityNotifyListenerEnd();
        }
        return self::$ACTIVITY_NOTIFY_LISTENER_END;
    }

    public static function activityEnd(): PvmAtomicOperationInterface
    {
        if (self::$ACTIVITY_END === null) {
            self::$ACTIVITY_END = new PvmAtomicOperationActivityEnd();
        }
        return self::$ACTIVITY_END;
    }

    public static function fireActivityEnd(): PvmAtomicOperationInterface
    {
        if (self::$FIRE_ACTIVITY_END === null) {
            self::$FIRE_ACTIVITY_END = new PvmAtomicOperationFireActivityEnd();
        }
        return self::$FIRE_ACTIVITY_END;
    }

    private static $TRANSITION_NOTIFY_LISTENER_END;
    private static $TRANSITION_DESTROY_SCOPE;
    private static $TRANSITION_NOTIFY_LISTENER_TAKE;
    private static $TRANSITION_START_NOTIFY_LISTENER_TAKE;
    private static $TRANSITION_CREATE_SCOPE;
    private static $TRANSITION_INTERRUPT_FLOW_SCOPE;
    private static $TRANSITION_NOTIFY_LISTENER_START;

    public static function transitionNotifyListenerEnd(): PvmAtomicOperationInterface
    {
        if (self::$TRANSITION_NOTIFY_LISTENER_END === null) {
            self::$TRANSITION_NOTIFY_LISTENER_END = new PvmAtomicOperationTransitionNotifyListenerEnd();
        }
        return self::$TRANSITION_NOTIFY_LISTENER_END;
    }

    public static function transitionDestroyScope(): PvmAtomicOperationInterface
    {
        if (self::$TRANSITION_DESTROY_SCOPE === null) {
            self::$TRANSITION_DESTROY_SCOPE = new PvmAtomicOperationTransitionDestroyScope();
        }
        return self::$TRANSITION_DESTROY_SCOPE;
    }

    public static function transitionNotifyListenerTake(): PvmAtomicOperationInterface
    {
        if (self::$TRANSITION_NOTIFY_LISTENER_TAKE === null) {
            self::$TRANSITION_NOTIFY_LISTENER_TAKE = new PvmAtomicOperationTransitionNotifyListenerTake();
        }
        return self::$TRANSITION_NOTIFY_LISTENER_TAKE;
    }

    public static function transitionStartNotifyListenerTake(): PvmAtomicOperationInterface
    {
        if (self::$TRANSITION_START_NOTIFY_LISTENER_TAKE === null) {
            self::$TRANSITION_START_NOTIFY_LISTENER_TAKE = new PvmAtomicOperationStartTransitionNotifyListenerTake();
        }
        return self::$TRANSITION_START_NOTIFY_LISTENER_TAKE;
    }

    public static function transitionCreateScope(): PvmAtomicOperationInterface
    {
        if (self::$TRANSITION_CREATE_SCOPE === null) {
            self::$TRANSITION_CREATE_SCOPE = new PvmAtomicOperationTransitionCreateScope();
        }
        return self::$TRANSITION_CREATE_SCOPE;
    }

    public static function transitionInterruptFlowScope(): PvmAtomicOperationInterface
    {
        if (self::$TRANSITION_INTERRUPT_FLOW_SCOPE === null) {
            self::$TRANSITION_INTERRUPT_FLOW_SCOPE = new PvmAtomicOperationsTransitionInterruptFlowScope();
        }
        return self::$TRANSITION_INTERRUPT_FLOW_SCOPE;
    }

    public static function transitionNotifyListenerStart(): PvmAtomicOperationInterface
    {
        if (self::$TRANSITION_NOTIFY_LISTENER_START === null) {
            self::$TRANSITION_NOTIFY_LISTENER_START = new PvmAtomicOperationTransitionNotifyListenerStart();
        }
        return self::$TRANSITION_NOTIFY_LISTENER_START;
    }

    private static $DELETE_CASCADE;
    private static $DELETE_CASCADE_FIRE_ACTIVITY_END;

    public static function deleteCascade(): PvmAtomicOperationInterface
    {
        if (self::$DELETE_CASCADE === null) {
            self::$DELETE_CASCADE = new PvmAtomicOperationDeleteCascade();
        }
        return self::$DELETE_CASCADE;
    }

    public static function deleteCascadeFireActivityEnd(): PvmAtomicOperationInterface
    {
        if (self::$DELETE_CASCADE_FIRE_ACTIVITY_END === null) {
            self::$DELETE_CASCADE_FIRE_ACTIVITY_END = new PvmAtomicOperationDeleteCascadeFireActivityEnd();
        }
        return self::$DELETE_CASCADE_FIRE_ACTIVITY_END;
    }

    private static $ACTIVITY_LEAVE;

    public static function activityLeave(): PvmAtomicOperationInterface
    {
        if (self::$ACTIVITY_LEAVE === null) {
            self::$ACTIVITY_LEAVE = new PvmAtomicOperationActivityLeave();
        }
        return self::$ACTIVITY_LEAVE;
    }
}
