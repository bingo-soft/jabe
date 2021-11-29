<?php

namespace BpmPlatform\Engine\Impl\Bpmn\Helper;

use BpmPlatform\Engine\Impl\Core\Model\{
    Properties,
    PropertyKey,
    PropertyListKey,
    PropertyMapKey
};

class BpmnProperties
{
    public static $TYPE;

    public static function type(): PropertyKey
    {
        if (self::$TYPE == null) {
            self::$TYPE = new PropertyKey("type");
        }
        return self::$TYPE;
    }

    public static $ESCALATION_EVENT_DEFINITIONS;

    public static function escalationEventDefinitions(): PropertyListKey
    {
        if (self::$ESCALATION_EVENT_DEFINITIONS == null) {
            self::$ESCALATION_EVENT_DEFINITIONS = new PropertyListKey("escalationEventDefinitions");
        }
        return self::$ESCALATION_EVENT_DEFINITIONS;
    }

    public static $ERROR_EVENT_DEFINITIONS;

    public static function errorEventDefinitions(): PropertyListKey
    {
        if (self::$ERROR_EVENT_DEFINITIONS == null) {
            self::$ERROR_EVENT_DEFINITIONS = new PropertyListKey("errorEventDefinitions");
        }
        return self::$ERROR_EVENT_DEFINITIONS;
    }

    /**
     * Declaration indexed by activity that is triggered by the event; assumes that there is at most one such declaration per activity.
     * There is code that relies on this assumption (e.g. when determining which declaration matches a job in the migration logic).
     */
    public static $TIMER_DECLARATIONS;

    public static function timerDeclarations(): PropertyMapKey
    {
        if (self::$TIMER_DECLARATIONS == null) {
            self::$TIMER_DECLARATIONS = new PropertyMapKey("timerDeclarations", false);
        }
        return self::$TIMER_DECLARATIONS;
    }

    /**
     * Declaration indexed by activity and listener (id) that is triggered by the event; there can be multiple such declarations per activity but only one per listener.
     * There is code that relies on this assumption (e.g. when determining which declaration matches a job in the migration logic).
     */
    public static $TIMEOUT_LISTENER_DECLARATIONS;

    public static function timeoutListenerDeclarations(): PropertyMapKey
    {
        if (self::$TIMEOUT_LISTENER_DECLARATIONS == null) {
            self::$TIMEOUT_LISTENER_DECLARATIONS = new PropertyMapKey("timerListenerDeclarations", false);
        }
        return self::$TIMEOUT_LISTENER_DECLARATIONS;
    }

    /**
     * Declaration indexed by activity that is triggered by the event; assumes that there is at most one such declaration per activity.
     * There is code that relies on this assumption (e.g. when determining which declaration matches a job in the migration logic).
     */
    public static $EVENT_SUBSCRIPTION_DECLARATIONS;

    public static function eventSubscriptionDeclarations(): PropertyMapKey
    {
        if (self::$EVENT_SUBSCRIPTION_DECLARATIONS == null) {
            self::$EVENT_SUBSCRIPTION_DECLARATIONS = new PropertyMapKey("eventDefinitions", false);
        }
        return self::$EVENT_SUBSCRIPTION_DECLARATIONS;
    }

    public static $COMPENSATION_BOUNDARY_EVENT;

    public static function compensationBoundaryEvent(): PropertyKey
    {
        if (self::$COMPENSATION_BOUNDARY_EVENT == null) {
            self::$COMPENSATION_BOUNDARY_EVENT = new PropertyKey("compensationBoundaryEvent");
        }
        return self::$COMPENSATION_BOUNDARY_EVENT;
    }

    public static $INITIAL_ACTIVITY;

    public static function initialActivity(): PropertyKey
    {
        if (self::$INITIAL_ACTIVITY == null) {
            self::$INITIAL_ACTIVITY = new PropertyKey("initial");
        }
        return self::$INITIAL_ACTIVITY;
    }

    public static $TRIGGERED_BY_EVENT;

    public static function triggeredByEvent(): PropertyKey
    {
        if (self::$TRIGGERED_BY_EVENT == null) {
            self::$TRIGGERED_BY_EVENT = new PropertyKey("triggeredByEvent");
        }
        return self::$TRIGGERED_BY_EVENT;
    }

    public static $HAS_CONDITIONAL_EVENTS;

    public static function hasConditionalEvents(): PropertyKey
    {
        if (self::$HAS_CONDITIONAL_EVENTS == null) {
            self::$HAS_CONDITIONAL_EVENTS = new PropertyKey("hasConditionalEvents");
        }
        return self::$HAS_CONDITIONAL_EVENTS;
    }

    public static $CONDITIONAL_EVENT_DEFINITION;

    public static function conditionalEventDefinition(): PropertyKey
    {
        if (self::$CONDITIONAL_EVENT_DEFINITION == null) {
            self::$CONDITIONAL_EVENT_DEFINITION = new PropertyKey("conditionalEventDefinition");
        }
        return self::$CONDITIONAL_EVENT_DEFINITION;
    }

    public static $EXTENSION_PROPERTIES;

    public static function extensionProperties(): PropertyKey
    {
        if (self::$EXTENSION_PROPERTIES == null) {
            self::$EXTENSION_PROPERTIES = new PropertyKey("extensionProperties");
        }
        return self::$EXTENSION_PROPERTIES;
    }

    public static $EXTENSION_ERROR_EVENT_DEFINITION;

    public static function extensionErrorEventDefinition(): PropertyListKey
    {
        if (self::$EXTENSION_ERROR_EVENT_DEFINITION == null) {
            self::$EXTENSION_ERROR_EVENT_DEFINITION = new PropertyListKey("extensionErrorEventDefinition");
        }
        return self::$EXTENSION_ERROR_EVENT_DEFINITION;
    }
}
