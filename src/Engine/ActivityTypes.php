<?php

namespace BpmPlatform\Engine;

/**
 * Class contains constants that identifies the activity types.
 * Events, gateways and activities are summed together as activities.
 * They typically correspond to the XML tags used in the BPMN 2.0 process definition file.
 *
 */
class ActivityTypes
{
    public const MULTI_INSTANCE_BODY = "multiInstanceBody";

    //gateways

    public const GATEWAY_EXCLUSIVE = "exclusiveGateway";
    public const GATEWAY_INCLUSIVE = "inclusiveGateway";
    public const GATEWAY_PARALLEL = "parallelGateway";
    public const GATEWAY_COMPLEX = "complexGateway";
    public const GATEWAY_EVENT_BASED = "eventBasedGateway";

    //tasks

    public const TASK = "task";
    public const TASK_SCRIPT = "scriptTask";
    public const TASK_SERVICE = "serviceTask";
    public const TASK_BUSINESS_RULE = "businessRuleTask";
    public const TASK_MANUAL_TASK = "manualTask";
    public const TASK_USER_TASK = "userTask";
    public const TASK_SEND_TASK = "sendTask";
    public const TASK_RECEIVE_TASK = "receiveTask";

    //other

    public const SUB_PROCESS = "subProcess";
    public const SUB_PROCESS_AD_HOC = "adHocSubProcess";
    public const CALL_ACTIVITY = "callActivity";
    public const TRANSACTION = "transaction";

    //boundary events

    public const BOUNDARY_TIMER = "boundaryTimer";
    public const BOUNDARY_MESSAGE = "boundaryMessage";
    public const BOUNDARY_SIGNAL = "boundarySignal";
    public const BOUNDARY_COMPENSATION = "compensationBoundaryCatch";
    public const BOUNDARY_ERROR = "boundaryError";
    public const BOUNDARY_ESCALATION = "boundaryEscalation";
    public const BOUNDARY_CANCEL = "cancelBoundaryCatch";
    public const BOUNDARY_CONDITIONAL = "boundaryConditional";

    //start events

    public const START_EVENT = "startEvent";
    public const START_EVENT_TIMER = "startTimerEvent";
    public const START_EVENT_MESSAGE = "messageStartEvent";
    public const START_EVENT_SIGNAL = "signalStartEvent";
    public const START_EVENT_ESCALATION = "escalationStartEvent";
    public const START_EVENT_COMPENSATION = "compensationStartEvent";
    public const START_EVENT_ERROR = "errorStartEvent";
    public const START_EVENT_CONDITIONAL = "conditionalStartEvent";

    //intermediate catch events

    public const INTERMEDIATE_EVENT_CATCH = "intermediateCatchEvent";
    public const INTERMEDIATE_EVENT_MESSAGE = "intermediateMessageCatch";
    public const INTERMEDIATE_EVENT_TIMER = "intermediateTimer";
    public const INTERMEDIATE_EVENT_LINK = "intermediateLinkCatch";
    public const INTERMEDIATE_EVENT_SIGNAL = "intermediateSignalCatch";
    public const INTERMEDIATE_EVENT_CONDITIONAL = "intermediateConditional";

    //intermediate throw events

    public const INTERMEDIATE_EVENT_THROW = "intermediateThrowEvent";
    public const INTERMEDIATE_EVENT_SIGNAL_THROW = "intermediateSignalThrow";
    public const INTERMEDIATE_EVENT_COMPENSATION_THROW = "intermediateCompensationThrowEvent";
    public const INTERMEDIATE_EVENT_MESSAGE_THROW = "intermediateMessageThrowEvent";
    public const INTERMEDIATE_EVENT_NONE_THROW = "intermediateNoneThrowEvent";
    public const INTERMEDIATE_EVENT_ESCALATION_THROW = "intermediateEscalationThrowEvent";

    //end events

    public const END_EVENT_ERROR = "errorEndEvent";
    public const END_EVENT_CANCEL = "cancelEndEvent";
    public const END_EVENT_TERMINATE = "terminateEndEvent";
    public const END_EVENT_MESSAGE = "messageEndEvent";
    public const END_EVENT_SIGNAL = "signalEndEvent";
    public const END_EVENT_COMPENSATION = "compensationEndEvent";
    public const END_EVENT_ESCALATION = "escalationEndEvent";
    public const END_EVENT_NONE = "noneEndEvent";
}
