<?php

namespace BpmPlatform\Engine\Impl\Bpmn\Parser;

use BpmPlatform\Engine\{
    ActivityTypes,
    BpmnParseException,
    ProcessEngineException
};
use BpmPlatform\Engine\Delegate\{
    ExecutionListenerInterface,
    TaskListenerInterface,
    VariableListenerInterface
};
use BpmPlatform\Engine\Impl\{
    ConditionInterface,
    ProcessEngineLogger
};
use BpmPlatform\Engine\Impl\Bpmn\Behavior\{
    BoundaryConditionalEventActivityBehavior,
    BoundaryEventActivityBehavior,
    CallActivityBehavior,
    CallableElementActivityBehavior,
    CancelBoundaryEventActivityBehavior,
    CancelEndEventActivityBehavior,
    ClassDelegateActivityBehavior,
    CompensationEventActivityBehavior,
    ErrorEndEventActivityBehavior,
    EventBasedGatewayActivityBehavior,
    EventSubProcessActivityBehavior,
    EventSubProcessStartConditionalEventActivityBehavior,
    EventSubProcessStartEventActivityBehavior,
    ExclusiveGatewayActivityBehavior,
    ExternalTaskActivityBehavior,
    InclusiveGatewayActivityBehavior,
    IntermediateCatchEventActivityBehavior,
    IntermediateCatchLinkEventActivityBehavior,
    IntermediateConditionalEventBehavior,
    IntermediateThrowNoneEventActivityBehavior,
    MailActivityBehavior,
    ManualTaskActivityBehavior,
    MultiInstanceActivityBehavior,
    NoneEndEventActivityBehavior,
    NoneStartEventActivityBehavior,
    ParallelGatewayActivityBehavior,
    ParallelMultiInstanceActivityBehavior,
    ReceiveTaskActivityBehavior,
    ScriptTaskActivityBehavior,
    SequentialMultiInstanceActivityBehavior,
    ServiceTaskDelegateExpressionActivityBehavior,
    ServiceTaskExpressionActivityBehavior,
    ShellActivityBehavior,
    SubProcessActivityBehavior,
    TaskActivityBehavior,
    TerminateEndEventActivityBehavior,
    ThrowEscalationEventActivityBehavior,
    ThrowSignalEventActivityBehavior,
    UserTaskActivityBehavior
};
use BpmPlatform\Engine\Impl\Bpmn\Helper\BpmnProperties;
use BpmPlatform\Engine\Impl\Bpmn\Listener\{
    ClassDelegateExecutionListener,
    DelegateExpressionExecutionListener,
    ExpressionExecutionListener,
    ScriptExecutionListener
};
use BpmPlatform\Engine\Impl\Context\Context;
use BpmPlatform\Engine\Impl\Core\Model\{
    BaseCallableElement,
    CallableElementBinding,
    CallableElementParameter,
    Properties
};
use BpmPlatform\Engine\Impl\Core\Variable\Mapping\IoMapping;
use BpmPlatform\Engine\Impl\Core\Variable\Value\{
    ConstantValueProvider,
    NullValueProvider,
    ParameterValueProvider
};
use BpmPlatform\Engine\Impl\El\{
    ElValueProvider,
    ExpressionInterface,
    ExpressionManager,
    FixedValue,
    UelExpressionCondition
};
use BpmPlatform\Engine\Impl\Event\EventType;
use BpmPlatform\Engine\Impl\Persistence\Entity\{
    DeploymentEntity,
    JobEntity,
    ProcessDefinitionEntity
};
use BpmPlatform\Engine\Impl\Pvm\PvmTransitionInterface;
use BpmPlatform\Engine\Impl\Pvm\Delegate\ActivityBehaviorInterface;
use BpmPlatform\Engine\Impl\Pvm\Runtime\LegacyBehavior;
use BpmPlatform\Engine\Impl\Scripting\{
    ExecutableScript,
    ScriptCondition
};
use BpmPlatform\Engine\Impl\Scripting\Engine\ScriptingEngines;
use BpmPlatform\Engine\Impl\Task\{
    TaskDecorator,
    TaskDefinition
};
use BpmPlatform\Engine\Impl\Task\Listener\{
    ClassDelegateTaskListener,
    DelegateExpressionTaskListener,
    ExpressionTaskListener,
    ScriptTaskListener
};
use BpmPlatform\Engine\Impl\Util\{
    ParseUtil,
    ReflectUtil,
    ScriptUtil,
    StringUtil
};
use BpmPlatform\Engine\Impl\Util\Xml\{
    Element,
    Parse
};
use BpmPlatform\Engine\Impl\Variable\VariableDeclaration;
use BpmPlatform\Engine\Repository\ProcessDefinitionInterface;

class BpmnParse extends Parse
{
    public const MULTI_INSTANCE_BODY_ID_SUFFIX = "#multiInstanceBody";

    //protected static final BpmnParseLogger LOG = ProcessEngineLogger.BPMN_PARSE_LOGGER;

    public const PROPERTYNAME_DOCUMENTATION = "documentation";
    public const PROPERTYNAME_INITIATOR_VARIABLE_NAME = "initiatorVariableName";
    public const PROPERTYNAME_HAS_CONDITIONAL_EVENTS = "hasConditionalEvents";
    public const PROPERTYNAME_CONDITION = "condition";
    public const PROPERTYNAME_CONDITION_TEXT = "conditionText";
    public const PROPERTYNAME_VARIABLE_DECLARATIONS = "variableDeclarations";
    public const PROPERTYNAME_TIMER_DECLARATION = "timerDeclarations";
    public const PROPERTYNAME_MESSAGE_JOB_DECLARATION = "messageJobDeclaration";
    public const PROPERTYNAME_ISEXPANDED = "isExpanded";
    public const PROPERTYNAME_START_TIMER = "timerStart";
    public const PROPERTYNAME_COMPENSATION_HANDLER_ID = "compensationHandler";
    public const PROPERTYNAME_IS_FOR_COMPENSATION = "isForCompensation";
    public const PROPERTYNAME_EVENT_SUBSCRIPTION_JOB_DECLARATION = "eventJobDeclarations";
    public const PROPERTYNAME_THROWS_COMPENSATION = "throwsCompensation";
    public const PROPERTYNAME_CONSUMES_COMPENSATION = "consumesCompensation";
    public const PROPERTYNAME_JOB_PRIORITY = "jobPriority";
    public const PROPERTYNAME_TASK_PRIORITY = "taskPriority";
    public const PROPERTYNAME_EXTERNAL_TASK_TOPIC = "topic";
    public const PROPERTYNAME_CLASS = "class";
    public const PROPERTYNAME_EXPRESSION = "expression";
    public const PROPERTYNAME_DELEGATE_EXPRESSION = "delegateExpression";
    public const PROPERTYNAME_VARIABLE_MAPPING_CLASS = "variableMappingClass";
    public const PROPERTYNAME_VARIABLE_MAPPING_DELEGATE_EXPRESSION = "variableMappingDelegateExpression";
    public const PROPERTYNAME_RESOURCE = "resource";
    public const PROPERTYNAME_LANGUAGE = "language";
    public const TYPE = "type";

    public const TRUE = "true";
    public const INTERRUPTING = "isInterrupting";

    public const CONDITIONAL_EVENT_DEFINITION = "conditionalEventDefinition";
    public const ESCALATION_EVENT_DEFINITION = "escalationEventDefinition";
    public const COMPENSATE_EVENT_DEFINITION = "compensateEventDefinition";
    public const TIMER_EVENT_DEFINITION = "timerEventDefinition";
    public const SIGNAL_EVENT_DEFINITION = "signalEventDefinition";
    public const MESSAGE_EVENT_DEFINITION = "messageEventDefinition";
    public const ERROR_EVENT_DEFINITION = "errorEventDefinition";
    public const CANCEL_EVENT_DEFINITION = "cancelEventDefinition";
    public const LINK_EVENT_DEFINITION = "linkEventDefinition";
    public const CONDITION_EXPRESSION = "conditionExpression";
    public const CONDITION = "condition";

    public const VARIABLE_EVENTS = [
        VariableListenerInterface::CREATE,
        VariableListenerInterface::DELETE,
        VariableListenerInterface::UPDATE
    ];

    /* process start authorization specific finals */
    protected const POTENTIAL_STARTER = "potentialStarter";
    protected const CANDIDATE_STARTER_USERS_EXTENSION = "candidateStarterUsers";
    protected const CANDIDATE_STARTER_GROUPS_EXTENSION = "candidateStarterGroups";

    protected const ATTRIBUTEVALUE_T_FORMAL_EXPRESSION = BpmnParser::BPMN20_NS . ":tFormalExpression";

    public const PROPERTYNAME_IS_MULTI_INSTANCE = "isMultiInstance";

    public const BPMN_EXTENSIONS_NS_PREFIX = BpmnParser::BPMN_EXTENSIONS_NS_PREFIX;
    public const XSI_NS_PREFIX = BpmnParser::XSI_NS_PREFIX;
    public const BPMN_DI_NS_PREFIX = BpmnParser::BPMN_DI_NS_PREFIX;
    public const OMG_DI_NS_PREFIX = BpmnParser::OMG_DI_NS_PREFIX;
    public const BPMN_DC_NS_PREFIX = BpmnParser::BPMN_DC_NS_PREFIX;
    public const ALL = "all";

    /** The deployment to which the parsed process definitions will be added. */
    protected $deployment;

    /** The end result of the parsing: a list of process definition. */
    protected $processDefinitions = [];

    /** Mapping of found errors in BPMN 2.0 file */
    protected $errors = [];

    /** Mapping of found escalation elements */
    protected $escalations = [];

    /**
     * Mapping from a process definition key to his containing list of job
     * declarations
     **/
    protected $jobDeclarations = [];

    /** A map for storing sequence flow based on their id during parsing. */
    protected $sequenceFlows = [];

    /**
     * A list of all element IDs. This allows us to parse only what we actually
     * support but still validate the references among elements we do not support.
     */
    protected $elementIds = [];

    /** A map for storing the process references of participants */
    protected $participantProcesses = [];

    /**
     * Mapping containing values stored during the first phase of parsing since
     * other elements can reference these messages.
     *
     * All the map's elements are defined outside the process definition(s), which
     * means that this map doesn't need to be re-initialized for each new process
     * definition.
     */
    protected $messages = [];
    protected $signals = [];

    // Members
    protected $expressionManager;
    protected $parseListeners = [];
    protected $importers = [];
    protected $prefixs = [];
    protected $targetNamespace;

    private $eventLinkTargets = [];
    private $eventLinkSources = [];
}
