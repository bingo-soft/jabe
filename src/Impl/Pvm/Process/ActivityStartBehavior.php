<?php

namespace Jabe\Impl\Pvm\Process;

class ActivityStartBehavior
{
    /**
     * Default start behavior for an activity is to "do nothing special". Meaning:
     * the activity is executed by the execution which enters it.
     *
     * NOTE: Only activities contained in normal flow can have DEFALUT start behavior.
     */
    public const DEFAULT = "default";

    /**
     * Used for activities which {@link PvmExecutionImpl#interrupt(String) interrupt}
     * their {@link PvmActivity#getFlowScope() flow scope}. Examples:
     * - Terminate end event
     * - Cancel end event
     *
     * NOTE: can only be used for activities contained in normal flow
     */
    public const INTERRUPT_FLOW_SCOPE = "interrupt_flow_scope";

    /**
     * Used for activities which are executed concurrently to activities
     * within the same {@link ActivityImpl#getFlowScope() flowScope}.
     */
    public const CONCURRENT_IN_FLOW_SCOPE = "concurrent_in_flow_scope";

    /**
     * Used for activities which {@link PvmExecutionImpl#interrupt(String) interrupt}
     * their {@link PvmActivity#getEventScope() event scope}
     *
     * NOTE: cannot only be used for activities contained in normal flow
     */
    public const INTERRUPT_EVENT_SCOPE = "interrupt_event_scope";

    /**
     * Used for activities which cancel their {@link PvmActivity#getEventScope() event scope}.
     * - Boundary events with cancelActivity=true
     *
     * NOTE: cannot only be used for activities contained in normal flow
     */
    public const CANCEL_EVENT_SCOPE = "cancel_event_scope";
}
