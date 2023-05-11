<?php

namespace Jabe\Impl\Bpmn\Parser;

use Jabe\{
    ActivityTypes,
    BpmnParseException,
    ProcessEngineException
};
use Jabe\Delegate\{
    ExecutionListenerInterface,
    TaskListenerInterface,
    VariableListenerInterface
};
use Jabe\Impl\{
    ConditionInterface,
    ProcessEngineLogger
};
use Jabe\Impl\Bpmn\Behavior\{
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
use Jabe\Impl\Bpmn\Helper\BpmnProperties;
use Jabe\Impl\Bpmn\Listener\{
    ClassDelegateExecutionListener,
    DelegateExpressionExecutionListener,
    ExpressionExecutionListener,
    ScriptExecutionListener
};
use Jabe\Impl\Context\Context;
use Jabe\Impl\Core\Model\{
    BaseCallableElement,
    CallableElement,
    CallableElementBinding,
    CallableElementParameter,
    Properties
};
use Jabe\Impl\Core\Variable\Mapping\IoMapping;
use Jabe\Impl\Core\Variable\Mapping\Value\{
    ConstantValueProvider,
    NullValueProvider,
    ParameterValueProviderInterface
};
use Jabe\Impl\Cxf\Webservice\CxfWSDLImporter;
use Jabe\Impl\El\{
    ElValueProvider,
    ExpressionInterface,
    ExpressionManagerInterface,
    FixedValue,
    UelExpressionCondition
};
use Jabe\Impl\Event\EventType;
use Jabe\Impl\Form\FormDefinition;
use Jabe\Impl\Form\Handler\{
    DefaultStartFormHandler,
    DefaultTaskFormHandler,
    DelegateStartFormHandler,
    DelegateTaskFormHandler,
    StartFormHandlerInterface,
    TaskFormHandlerInterface
};
use Jabe\Impl\JobExecutor\{
    AsyncAfterMessageJobDeclaration,
    AsyncBeforeMessageJobDeclaration,
    EventSubscriptionJobDeclaration,
    JobDeclaration,
    MessageJobDeclaration,
    TimerCatchIntermediateEventJobHandler,
    TimerDeclarationImpl,
    TimerDeclarationType,
    TimerEventJobHandler,
    TimerExecuteNestedActivityJobHandler,
    TimerStartEventJobHandler,
    TimerStartEventSubprocessJobHandler,
    TimerTaskListenerJobHandler
};
use Jabe\Impl\Persistence\Entity\{
    DeploymentEntity,
    JobEntity,
    ProcessDefinitionEntity
};
use Jabe\Impl\Pvm\Process\{
    ActivityImpl,
    ActivityStartBehavior,
    AsyncAfterUpdateInterface,
    AsyncBeforeUpdateInterface,
    BacklogErrorCallbackInterface,
    HasDIBoundsInterface,
    Lane,
    LaneSet,
    ParticipantProcess,
    ProcessDefinitionImpl,
    ScopeImpl,
    TransitionImpl
};
use Jabe\Impl\Pvm\Runtime\LegacyBehavior;
use Jabe\Impl\Scripting\{
    ExecutableScript,
    ScriptCondition
};
use Jabe\Impl\Scripting\Engine\ScriptingEngines;
use Jabe\Impl\Task\{
    TaskDecorator,
    TaskDefinition
};
use Jabe\Impl\Task\Listener\{
    ClassDelegateTaskListener,
    DelegateExpressionTaskListener,
    ExpressionTaskListener,
    ScriptTaskListener
};
use Jabe\Impl\Util\{
    ClassDelegateUtil,
    ParseUtil,
    ReflectUtil,
    ScriptUtil,
    StringUtil
};
use Sax\{
    Element,
    Parse,
    XmlNamespace
};
use Jabe\Impl\Variable\VariableDeclaration;
use Jabe\Repository\ProcessDefinitionInterface;

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

    public const BPMN_DI_NS = BpmnParser::BPMN_DI_NS;
    public const BPMN_DC_NS = BpmnParser::BPMN_DC_NS;
    public const OMG_DI_NS = BpmnParser::OMG_DI_NS;

    /** The deployment to which the parsed process definitions will be added. */
    protected $deployment;

    /** The end result of the parsing: a list of process definition. */
    protected $processDefinitions = [];

    /** Mapping of found errors in BPMN 2.0 file */
    protected $errorsMap = [];

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

    public function __construct(BpmnParser $parser)
    {
        parent::__construct($parser);
        $this->expressionManager = $this->parser->getExpressionManager();
        $this->parseListeners = $this->parser->getParseListeners();

        //@TODO
        //$this->setSchemaResource(ReflectUtil::getResourceUrlAsString(BpmnParser::BPMN_20_SCHEMA_LOCATION));
    }

    public function deployment(DeploymentEntity $deployment): BpmnParse
    {
        $this->deployment = $deployment;
        return $this;
    }

    public function execute(): BpmnParse
    {
        parent::execute(); // schema validation

        try {
            $this->parseRootElement();
        } catch (BpmnParseException $e) {
            $this->addError($e);
        } catch (\Throwable $e) {
            //LOG.parsingFailure(e);
            // ALL unexpected exceptions should bubble up since they are not handled
            // accordingly by underlying parse-methods and the process can't be
            // deployed
            //throw LOG.parsingProcessException(e);
            throw new \Exception("parsingProcessException: " . $e->getMessage());
        } finally {
            //@TODO
            //if ($this->hasWarnings()) {
            //    $this->logWarnings();
            //}
            if ($this->hasErrors()) {
                $this->throwExceptionForErrors();
            }
        }
        return $this;
    }

    /**
     * Parses the 'definitions' root element
     */
    protected function parseRootElement(): void
    {
        $this->collectElementIds();
        $this->parseDefinitionsAttributes();
        $this->parseImports();
        $this->parseMessages();
        $this->parseSignals();
        $this->parseErrors();
        $this->parseEscalations();
        $this->parseProcessDefinitions();
        $this->parseCollaboration();

        // Diagram interchange parsing must be after parseProcessDefinitions,
        // since it depends and sets values on existing process definition objects
        $this->parseDiagramInterchangeElements();

        foreach ($this->parseListeners as $parseListener) {
            $parseListener->parseRootElement($this->rootElement, $this->getProcessDefinitions());
        }
    }

    protected function collectElementIds(): void
    {
        $this->rootElement->collectIds($this->elementIds);
    }

    protected function parseDefinitionsAttributes(): void
    {
        $this->targetNamespace = $this->rootElement->attribute("targetNamespace");

        foreach ($this->rootElement->attributes() as $attribute) {
            if (str_starts_with(strtoupper($attribute), "XMLNS:")) {
                $prefixValue = $this->rootElement->attribute($attribute);
                $prefixName = substr($attribute, 6);
                $this->prefixs[$prefixName] = $prefixValue;
            }
        }
    }

    protected function resolveName(?string $name): ?string
    {
        if ($name === null) {
            return null;
        }
        $indexOfP = strpos($name, ':');
        if ($indexOfP !== false) {
            $prefix = substr($name, 0, $indexOfP);
            $resolvedPrefix = $this->prefixs[$prefix];
            return $resolvedPrefix . ":" . substr($name, $indexOfP + 1);
        } else {
            return $this->targetNamespace . ":" . $name;
        }
    }

    /**
     * Parses the rootElement importing structures
     */
    protected function parseImports(): void
    {
        $imports = $this->rootElement->elements("import");
        foreach ($imports as $theImport) {
            $importType = $theImport->attribute("importType");
            $importer = $this->getImporter($importType, $theImport);
            if ($importer === null) {
                $this->addError("Could not import item of type " . $importType, $theImport);
            } else {
                $importer->importFrom($theImport, $this);
            }
        }
    }

    protected function getImporter(?string $importType, Element $theImport): ?XMLImporterInterface
    {
        if (array_key_exists($importType, $this->importers)) {
            return $this->importers[$importType];
        } else {
            if ($importType == "http://schemas.xmlsoap.org/wsdl/") {
                try {
                    $newInstance = new CxfWSDLImporter();
                    $this->importers[$importType] = $newInstance;
                    return $newInstance;
                } catch (\Exception $e) {
                    $this->addError("Could not find importer for type " . $importType, $theImport);
                }
            }
            return null;
        }
    }

    /**
    * Parses the messages of the given definitions file. Messages are not
    * contained within a process element, but they can be referenced from inner
    * process elements.
    */
    public function parseMessages(): void
    {
        foreach ($this->rootElement->elements("message") as $messageElement) {
            $id = $messageElement->attribute("id");
            $messageName = $messageElement->attribute("name");

            $messageExpression = null;
            if ($messageName !== null) {
                $messageExpression = $this->expressionManager->createExpression($messageName);
            }

            $messageDefinition = new MessageDefinition($this->targetNamespace . ":" . $id, $messageExpression);
            $this->messages[$messageDefinition->getId()] = $messageDefinition;
        }
    }

    /**
    * Parses the signals of the given definitions file. Signals are not contained
    * within a process element, but they can be referenced from inner process
    * elements.
    */
    protected function parseSignals(): void
    {
        foreach ($this->rootElement->elements("signal") as $signalElement) {
            $id = $signalElement->attribute("id");
            $signalName = $signalElement->attribute("name");

            foreach ($this->signals as $signalDefinition) {
                if ($signalDefinition->getName() == $signalName) {
                    $this->addError("duplicate signal name '" . $signalName . "'.", $signalElement);
                }
            }

            if ($id === null) {
                $this->addError("signal must have an id", $signalElement);
            } elseif ($signalName === null) {
                $this->addError("signal with id '" . $id . "' has no name", $signalElement);
            } else {
                $signalExpression = $this->expressionManager->createExpression($signalName);
                $signal = new SignalDefinition();
                $signal->setId($this->targetNamespace . ":" . $id);
                $signal->setExpression($signalExpression);

                $this->signals[$signal->getId()] = $signal;
            }
        }
    }

    public function parseErrors(): void
    {
        foreach ($this->rootElement->elements("error") as $errorElement) {
            $error = new Error();

            $id = $errorElement->attribute("id");
            if ($id === null) {
                $this->addError("'id' is mandatory on error definition", $errorElement);
            }
            $error->setId($id);

            $errorCode = $errorElement->attribute("errorCode");
            if ($errorCode !== null) {
                $error->setErrorCode($errorCode);
            }

            $errorMessage = $errorElement->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, "errorMessage");
            if ($errorMessage !== null) {
                $error->setErrorMessageExpression($this->createParameterValueProvider($errorMessage, $this->expressionManager));
            }

            $this->errorsMap[$id] = $error;
        }
    }

    protected function parseEscalations(): void
    {
        foreach ($this->rootElement->elements("escalation") as $element) {
            $id = $element->attribute("id");
            if ($id === null) {
                $this->addError("escalation must have an id", $element);
            } else {
                $escalation = $this->createEscalation($id, $element);
                $this->escalations[$id] = $escalation;
            }
        }
    }

    protected function createEscalation(?string $id, Element $element): Escalation
    {
        $escalation = new Escalation($id);

        $name = $element->attribute("name");
        if ($name !== null) {
            $escalation->setName($name);
        }

        $escalationCode = $element->attribute("escalationCode");
        if ($escalationCode !== null && !empty($escalationCode)) {
            $escalation->setEscalationCode($escalationCode);
        }
        return $escalation;
    }

    /**
    * Parses all the process definitions defined within the 'definitions' root
    * element.
    */
    public function parseProcessDefinitions(): void
    {
        foreach ($this->rootElement->elements("process") as $processElement) {
            $isExecutable = !$this->deployment->isNew();
            $isExecutableStr = $processElement->attribute("isExecutable");
            if ($isExecutableStr !== null) {
                $isExecutable = $isExecutableStr === "true";
                if (!$isExecutable) {
                    //LOG.ignoringNonExecutableProcess(processElement->attribute("id"));
                }
            } else {
                //LOG.missingIsExecutableAttribute(processElement->attribute("id"));
            }

            // Only process executable processes
            if ($isExecutable) {
                $this->processDefinitions[] = $this->parseProcess($processElement);
            }
        }
    }

    /**
    * Parses the collaboration definition defined within the 'definitions' root
    * element and get all participants to lookup their process references during
    * DI parsing.
    */
    public function parseCollaboration(): void
    {
        $collaboration = $this->rootElement->element("collaboration");
        if ($collaboration !== null) {
            foreach ($collaboration->elements("participant") as $participant) {
                $processRef = $participant->attribute("processRef");
                if ($processRef !== null) {
                    $procDef = $this->getProcessDefinition($processRef);
                    if ($procDef !== null) {
                        // Set participant process on the procDef, so it can get rendered
                        // later on if needed
                        $participantProcess = new ParticipantProcess();
                        $participantProcess->setId($participant->attribute("id"));
                        $participantProcess->setName($participant->attribute("name"));
                        $procDef->setParticipantProcess($participantProcess);

                        $this->participantProcesses[$participantProcess->getId()] = $processRef;
                    }
                }
            }
        }
    }

    /**
    * Parses one process (ie anything inside a <process> element).
    *
    * @param processElement
    *          The 'process' element.
    * @return ProcessDefinitionEntity The parsed version of the XML: a ProcessDefinitionImpl
    *         object.
    */
    public function parseProcess(Element $processElement): ProcessDefinitionEntity
    {
        // reset all mappings that are related to one process definition
        $this->sequenceFlows = [];

        $processDefinition = new ProcessDefinitionEntity();

        /*
            * Mapping object model - bpmn xml: processDefinition.id -> generated by
            * processDefinition.key -> bpmn id (required) processDefinition.name ->
            * bpmn name (optional)
            */
        $processDefinition->setKey($processElement->attribute("id"));
        $processDefinition->setName($processElement->attribute("name"));
        $processDefinition->setCategory($this->rootElement->attribute("targetNamespace"));
        $processDefinition->setProperty(self::PROPERTYNAME_DOCUMENTATION, self::parseDocumentation($processElement));
        $processDefinition->setTaskDefinitions([]);
        $processDefinition->setDeploymentId($this->deployment->getId());
        $processDefinition->setTenantId($this->deployment->getTenantId());
        $processDefinition->setProperty(self::PROPERTYNAME_JOB_PRIORITY, $this->parsePriority($processElement, self::PROPERTYNAME_JOB_PRIORITY));
        $processDefinition->setProperty(self::PROPERTYNAME_TASK_PRIORITY, $this->parsePriority($processElement, self::PROPERTYNAME_TASK_PRIORITY));
        $processDefinition->setVersionTag($processElement->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, "versionTag"));

        try {
            $historyTimeToLive = $processElement->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, "historyTimeToLive", Context::getProcessEngineConfiguration()->getHistoryTimeToLive());
            $processDefinition->setHistoryTimeToLive(ParseUtil::parseHistoryTimeToLive($historyTimeToLive));
        } catch (\Exception $e) {
            $this->addError(new BpmnParseException($e->getMessage(), $processElement, $e));
        }

        $isStartableInTasklist = $this->isStartable($processElement);
        $processDefinition->setStartableInTasklist($isStartableInTasklist);

        //LOG.parsingElement("process", processDefinition->getKey());

        $this->parseScope($processElement, $processDefinition);

        // Parse any laneSets defined for this process
        $this->parseLaneSets($processElement, $processDefinition);

        foreach ($this->parseListeners as $parseListener) {
            $parseListener->parseProcess($processElement, $processDefinition);
        }

        // now we have parsed anything we can validate some stuff
        $this->validateActivities($processDefinition->getActivities());

        //unregister delegates
        foreach ($processDefinition->getActivities() as $activity) {
            $activity->setDelegateAsyncAfterUpdate(null);
            $activity->setDelegateAsyncBeforeUpdate(null);
        }

        return $processDefinition;
    }

    protected function parseLaneSets(Element $parentElement, ProcessDefinitionEntity $processDefinition): void
    {
        $laneSets = $parentElement->elements("laneSet");

        if (!empty($laneSets)) {
            foreach ($laneSets as $laneSetElement) {
                $newLaneSet = new LaneSet();

                $newLaneSet->setId($laneSetElement->attribute("id"));
                $newLaneSet->setName($laneSetElement->attribute("name"));
                $this->parseLanes($laneSetElement, $newLaneSet);

                // Finally, add the set
                $processDefinition->addLaneSet($newLaneSet);
            }
        }
    }

    protected function parseLanes(Element $laneSetElement, LaneSet $laneSet): void
    {
        $lanes = $laneSetElement->elements("lane");
        if (!empty($lanes)) {
            foreach ($lanes as $laneElement) {
                // Parse basic attributes
                $lane = new Lane();
                $lane->setId($laneElement->attribute("id"));
                $lane->setName($laneElement->attribute("name"));

                // Parse ID's of flow-nodes that live inside this lane
                $flowNodeElements = $laneElement->elements("flowNodeRef");
                if (!empty($flowNodeElements)) {
                    foreach ($flowNodeElements as $flowNodeElement) {
                        $lane->addFlowNodeId($flowNodeElement->getText());
                    }
                }

                $laneSet->addLane($lane);
            }
        }
    }

    /**
    * Parses a scope: a process, subprocess, etc.
    *
    * Note that a process definition is a scope on itself.
    *
    * @param scopeElement
    *          The XML element defining the scope
    * @param parentScope
    *          The scope that contains the nested scope.
    */
    public function parseScope(Element $scopeElement, ScopeImpl $parentScope): void
    {

        // Not yet supported on process level (PVM additions needed):
        // parseProperties(processElement);

        // filter activities that must be parsed separately
        $activityElements = $scopeElement->elements();
        $intermediateCatchEvents = $this->filterIntermediateCatchEvents($activityElements);
        foreach ($activityElements as $key => $activityElement) {
            foreach ($intermediateCatchEvents as $intermediateCatchEvent) {
                if ($activityElement == $intermediateCatchEvent) {
                    unset($activityElements[$key]);
                }
            }
        }
        $compensationHandlers = $this->filterCompensationHandlers($activityElements);
        foreach ($activityElements as $key => $activityElement) {
            foreach ($compensationHandlers as $compensationHandler) {
                if ($activityElement == $compensationHandler) {
                    unset($activityElements[$key]);
                }
            }
        }

        $this->parseStartEvents($scopeElement, $parentScope);
        $this->parseActivities($activityElements, $scopeElement, $parentScope);
        $this->parseIntermediateCatchEvents($scopeElement, $parentScope, $intermediateCatchEvents);
        $this->parseEndEvents($scopeElement, $parentScope);
        $this->parseBoundaryEvents($scopeElement, $parentScope);
        $this->parseSequenceFlow($scopeElement, $parentScope, $compensationHandlers);
        $this->parseExecutionListenersOnScope($scopeElement, $parentScope);
        $this->parseAssociations($scopeElement, $parentScope, $compensationHandlers);
        $this->parseCompensationHandlers($parentScope, $compensationHandlers);

        foreach ($parentScope->getBacklogErrorCallbacks() as $callback) {
            $callback->callback();
        }

        if ($parentScope instanceof ProcessDefinitionInterface) {
            $this->parseProcessDefinitionCustomExtensions($scopeElement, $parentScope);
        }
    }

    protected function filterIntermediateCatchEvents(array $activityElements): array
    {
        $intermediateCatchEvents = [];
        foreach ($activityElements as $activityElement) {
            if (strtoupper($activityElement->getTagName()) == strtoupper(ActivityTypes::INTERMEDIATE_EVENT_CATCH)) {
                $intermediateCatchEvents[$activityElement->attribute("id")] = $activityElement;
            }
        }
        return $intermediateCatchEvents;
    }

    protected function filterCompensationHandlers(array $activityElements): array
    {
        $compensationHandlers = [];
        foreach ($activityElements as $activityElement) {
            if ($this->isCompensationHandler($activityElement)) {
                $compensationHandlers[$activityElement->attribute("id")] = $activityElement;
            }
        }
        return $compensationHandlers;
    }

    protected function parseIntermediateCatchEvents(Element $scopeElement, ScopeImpl $parentScope, array &$intermediateCatchEventElements): void
    {
        foreach ($intermediateCatchEventElements as $intermediateCatchEventElement) {
            if ($parentScope->findActivity($intermediateCatchEventElement->attribute("id")) === null) {
                // check whether activity is already parsed
                $activity = $this->parseIntermediateCatchEvent($intermediateCatchEventElement, $parentScope, null);

                if ($activity !== null) {
                    $this->parseActivityInputOutput($intermediateCatchEventElement, $activity);
                }
            }
        }
        $intermediateCatchEventElements = [];
    }

    protected function parseProcessDefinitionCustomExtensions(Element $scopeElement, ProcessDefinitionInterface $definition): void
    {
        $this->parseStartAuthorization($scopeElement, $definition);
    }

    protected function parseStartAuthorization(Element $scopeElement, ProcessDefinitionInterface $definition): void
    {
        $processDefinition = $definition;

        // parse activiti:potentialStarters
        $extentionsElement = $scopeElement->element("extensionElements");
        if ($extentionsElement !== null) {
            $potentialStarterElements = $extentionsElement->elementsNS(self::BPMN_EXTENSIONS_NS_PREFIX, self::POTENTIAL_STARTER);

            foreach ($potentialStarterElements as $potentialStarterElement) {
                $this->parsePotentialStarterResourceAssignment($potentialStarterElement, $processDefinition);
            }
        }

        // parse activiti:candidateStarterUsers
        $candidateUsersString = $scopeElement->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, self::CANDIDATE_STARTER_USERS_EXTENSION);
        if ($candidateUsersString !== null) {
            $candidateUsers = $this->parseCommaSeparatedList($candidateUsersString);
            foreach ($candidateUsers as $candidateUser) {
                $processDefinition->addCandidateStarterUserIdExpression($this->expressionManager->createExpression(trim($candidateUser)));
            }
        }

        // Candidate activiti:candidateStarterGroups
        $candidateGroupsString = $scopeElement->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, self::CANDIDATE_STARTER_GROUPS_EXTENSION);
        if ($candidateGroupsString !== null) {
            $candidateGroups = $this->parseCommaSeparatedList($candidateGroupsString);
            foreach ($candidateGroups as $candidateGroup) {
                $processDefinition->addCandidateStarterGroupIdExpression($this->expressionManager->createExpression(trim($candidateGroup)));
            }
        }
    }

    protected function parsePotentialStarterResourceAssignment(Element $performerElement, ProcessDefinitionEntity $processDefinition): void
    {
        $raeElement = $performerElement->element(self::RESOURCE_ASSIGNMENT_EXPR);
        if ($raeElement !== null) {
            $feElement = $raeElement->element(self::FORMAL_EXPRESSION);
            if ($feElement !== null) {
                $assignmentExpressions = $this->parseCommaSeparatedList($feElement->getText());
                foreach ($assignmentExpressions as $assignmentExpression) {
                    $assignmentExpression = trim($assignmentExpression);
                    if (str_starts_with($assignmentExpression, self::USER_PREFIX)) {
                        $userAssignementId = $this->getAssignmentId($assignmentExpression, self::USER_PREFIX);
                        $processDefinition->addCandidateStarterUserIdExpression($this->expressionManager->createExpression($userAssignementId));
                    } elseif (str_starts_with($assignmentExpression, self::GROUP_PREFIX)) {
                        $groupAssignementId = $this->getAssignmentId($assignmentExpression, self::GROUP_PREFIX);
                        $processDefinition->addCandidateStarterGroupIdExpression($this->expressionManager->createExpression($groupAssignementId));
                    } else { // default: given string is a goupId, as-is.
                        $processDefinition->addCandidateStarterGroupIdExpression($this->expressionManager->createExpression($assignmentExpression));
                    }
                }
            }
        }
    }

    protected function parseAssociations(Element $scopeElement, ScopeImpl $parentScope, array &$compensationHandlers): void
    {
        foreach ($scopeElement->elements("association") as $associationElement) {
            $sourceRef = $associationElement->attribute("sourceRef");
            if ($sourceRef === null) {
                $this->addError("association element missing attribute 'sourceRef'", $associationElement);
            }
            $targetRef = $associationElement->attribute("targetRef");
            if ($targetRef === null) {
                $this->addError("association element missing attribute 'targetRef'", $associationElement);
            }
            $sourceActivity = $parentScope->findActivity($sourceRef);
            $targetActivity = $parentScope->findActivity($targetRef);
            // an association may reference elements that are not parsed as activities
            // (like for instance text annotations so do not throw an exception if sourceActivity or targetActivity are null)
            // However, we make sure they reference 'something':
            if ($sourceActivity === null && !in_array($sourceRef, $this->elementIds)) {
                $this->addError("Invalid reference sourceRef '" . $sourceRef . "' of association element ", $associationElement);
            } elseif ($targetActivity === null && !in_array($targetRef, $this->elementIds)) {
                $this->addError("Invalid reference targetRef '" . $targetRef . "' of association element ", $associationElement);
            } else {
                if ($sourceActivity !== null && ActivityTypes::BOUNDARY_COMPENSATION == $sourceActivity->getProperty(BpmnProperties::type()->getName())) {
                    if ($targetActivity === null && array_key_exists($targetRef, $compensationHandlers)) {
                        $targetActivity = $this->parseCompensationHandlerForCompensationBoundaryEvent($parentScope, $sourceActivity, $targetRef, $compensationHandlers);
                        foreach ($compensationHandlers as $key => $value) {
                            if ($key == $targetActivity->getId()) {
                                unset($compensationHandlers[$key]);
                            }
                        }
                    }

                    if ($targetActivity !== null) {
                        $this->parseAssociationOfCompensationBoundaryEvent($associationElement, $sourceActivity, $targetActivity);
                    }
                }
            }
        }
    }

    protected function parseCompensationHandlerForCompensationBoundaryEvent(ScopeImpl $parentScope, ActivityImpl $sourceActivity, ?string $targetRef, array $compensationHandlers): ActivityImpl
    {
        $compensationHandler = $compensationHandlers[$targetRef];

        $eventScope = $sourceActivity->getEventScope();
        $compensationHandlerActivity = null;
        if ($eventScope->isMultiInstance()) {
            $miBody = $eventScope->getFlowScope();
            $compensationHandlerActivity = $this->parseActivity($compensationHandler, null, $miBody);
        } else {
            $compensationHandlerActivity = $this->parseActivity($compensationHandler, null, $parentScope);
        }

        $compensationHandlerActivity->getProperties()->set(BpmnProperties::compensationBoundaryEvent(), $sourceActivity);
        return $compensationHandlerActivity;
    }

    protected function parseAssociationOfCompensationBoundaryEvent(Element $associationElement, ActivityImpl $sourceActivity, ActivityImpl $targetActivity): void
    {
        if (!$targetActivity->isCompensationHandler()) {
            $this->addError(
                "compensation boundary catch must be connected to element with isForCompensation=true",
                $associationElement,
                $sourceActivity->getId(),
                $targetActivity->getId()
            );
        } else {
            $compensatedActivity = $sourceActivity->getEventScope();

            $compensationHandler = $compensatedActivity->findCompensationHandler();
            if ($compensationHandler !== null && $compensationHandler->isSubProcessScope()) {
                $this->addError(
                    "compensation boundary event and event subprocess with compensation start event are not supported on the same scope",
                    $associationElement,
                    $compensatedActivity->getId(),
                    $sourceActivity->getId()
                );
            } else {
                $compensatedActivity->setProperty(self::PROPERTYNAME_COMPENSATION_HANDLER_ID, $targetActivity->getId());
            }
        }
    }

    protected function parseCompensationHandlers(ScopeImpl $parentScope, array &$compensationHandlers): void
    {
        // compensation handlers attached to compensation boundary events should be already parsed
        foreach ($compensationHandlers as $compensationHandler) {
            $this->parseActivity($compensationHandler, null, $parentScope);
        }
        $compensationHandlers = [];
    }

    /**
    * Parses the start events of a certain level in the process (process,
    * subprocess or another scope).
    *
    * @param parentElement
    *          The 'parent' element that contains the start events (process,
    *          subprocess).
    * @param scope
    *          The ScopeImpl to which the start events must be added.
    */
    public function parseStartEvents(Element $parentElement, ScopeImpl $scope): void
    {
        $startEventElements = $parentElement->elements("startEvent");
        $startEventActivities = [];
        if (count($startEventElements) > 0) {
            foreach ($startEventElements as $startEventElement) {
                $startEventActivity = $this->createActivityOnScope($startEventElement, $scope);
                $this->parseAsynchronousContinuationForActivity($startEventElement, $startEventActivity);

                if ($scope instanceof ProcessDefinitionEntity) {
                    $this->parseProcessDefinitionStartEvent($startEventActivity, $startEventElement, $parentElement, $scope);
                    $startEventActivities[] = $startEventActivity;
                } else {
                    $this->parseScopeStartEvent($startEventActivity, $startEventElement, $parentElement, $scope);
                }

                $this->ensureNoIoMappingDefined($startEventElement);

                $this->parseExecutionListenersOnScope($startEventElement, $startEventActivity);
            }
        } else {
            if (in_array($parentElement->getTagName(), ["process", "subProcess"])) {
                $this->addError($parentElement->getTagName() . " must define a startEvent element", $parentElement);
            }
        }
        if ($scope instanceof ProcessDefinitionEntity) {
            $this->selectInitial($startEventActivities, $scope, $parentElement);
            $this->parseStartFormHandlers($startEventElements, $scope);
        }

        // invoke parse listeners
        foreach ($startEventElements as $startEventElement) {
            $startEventActivity = $scope->getChildActivity($startEventElement->attribute("id"));
            foreach ($this->parseListeners as $parseListener) {
                $parseListener->parseStartEvent($startEventElement, $scope, $startEventActivity);
            }
        }
    }

    protected function selectInitial(array $startEventActivities, ProcessDefinitionEntity $processDefinition, Element $parentElement): void
    {
        $initial = null;
        // validate that there is s single none start event / timer start event:
        $exclusiveStartEventTypes = ["STARTEVENT", "STARTTIMEREVENT"];
        foreach ($startEventActivities as $activityImpl) {
            if (in_array(strtoupper($activityImpl->getProperty(BpmnProperties::type()->getName())), $exclusiveStartEventTypes)) {
                if ($initial === null) {
                    $initial = $activityImpl;
                } else {
                    $this->addError("multiple none start events or timer start events not supported on process definition", $parentElement, $activityImpl->getId());
                }
            }
        }
        // if there is a single start event, select it as initial, regardless of its type:
        if ($initial === null && count($startEventActivities) == 1) {
            $initial = $startEventActivities[0];
        }
        $processDefinition->setInitial($initial);
    }

    protected function parseProcessDefinitionStartEvent(ActivityImpl $startEventActivity, Element $startEventElement, Element $parentElement, ScopeImpl $scope): void
    {
        $processDefinition = $scope;

        $initiatorVariableName = $startEventElement->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, "initiator");
        if ($initiatorVariableName !== null) {
            $processDefinition->setProperty(self::PROPERTYNAME_INITIATOR_VARIABLE_NAME, $initiatorVariableName);
        }

        // all start events share the same behavior:
        $startEventActivity->setActivityBehavior(new NoneStartEventActivityBehavior());

        $timerEventDefinition = $startEventElement->element(self::TIMER_EVENT_DEFINITION);
        $messageEventDefinition = $startEventElement->element(self::MESSAGE_EVENT_DEFINITION);
        $signalEventDefinition = $startEventElement->element(self::SIGNAL_EVENT_DEFINITION);
        $conditionEventDefinition = $startEventElement->element(self::CONDITIONAL_EVENT_DEFINITION);
        if ($timerEventDefinition !== null) {
            $this->parseTimerStartEventDefinition($timerEventDefinition, $startEventActivity, $processDefinition);
        } elseif ($messageEventDefinition !== null) {
            $startEventActivity->getProperties()->set(BpmnProperties::type(), ActivityTypes::START_EVENT_MESSAGE);

            $messageStartEventSubscriptionDeclaration =
                $this->parseMessageEventDefinition($messageEventDefinition, $startEventElement->attribute("id"));
            $messageStartEventSubscriptionDeclaration->setActivityId($startEventActivity->getId());
            $messageStartEventSubscriptionDeclaration->setStartEvent(true);

            $this->ensureNoExpressionInMessageStartEvent($messageEventDefinition, $messageStartEventSubscriptionDeclaration, $startEventElement->attribute("id"));
            $this->addEventSubscriptionDeclaration($messageStartEventSubscriptionDeclaration, $processDefinition, $startEventElement);
        } elseif ($signalEventDefinition !== null) {
            $startEventActivity->getProperties()->set(BpmnProperties::type(), ActivityTypes::START_EVENT_SIGNAL);
            $startEventActivity->setEventScope($scope);

            $this->parseSignalCatchEventDefinition($signalEventDefinition, $startEventActivity, true);
        } elseif ($conditionEventDefinition !== null) {
            $startEventActivity->getProperties()->set(BpmnProperties::type(), ActivityTypes::START_EVENT_CONDITIONAL);

            $conditionalEventDefinition = $this->parseConditionalEventDefinition($conditionEventDefinition, $startEventActivity);
            $conditionalEventDefinition->setStartEvent(true);
            $conditionalEventDefinition->setActivityId($startEventActivity->getId());
            $startEventActivity->getProperties()->set(BpmnProperties::conditionalEventDefinition(), $conditionalEventDefinition);

            $this->addEventSubscriptionDeclaration($conditionalEventDefinition, $processDefinition, $startEventElement);
        }
    }

    protected function parseStartFormHandlers(array $startEventElements, ProcessDefinitionEntity $processDefinition): void
    {
        if ($processDefinition->getInitial() !== null) {
            foreach ($startEventElements as $startEventElement) {
                if ($startEventElement->attribute("id") == $processDefinition->getInitial()->getId()) {
                    $startFormHandler = null;
                    $startFormHandlerClassName = $startEventElement->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, "formHandlerClass");
                    if ($startFormHandlerClassName !== null) {
                        $startFormHandler = ReflectUtil::instantiate($startFormHandlerClassName);
                    } else {
                        $startFormHandler = new DefaultStartFormHandler();
                    }

                    $startFormHandler->parseConfiguration($startEventElement, $this->deployment, $processDefinition, $this);

                    $processDefinition->setStartFormHandler(new DelegateStartFormHandler($startFormHandler, $this->deployment));

                    $formDefinition = $this->parseFormDefinition($startEventElement);
                    $processDefinition->setStartFormDefinition($formDefinition);

                    $processDefinition->setHasStartFormKey($formDefinition->getFormKey() !== null);
                }
            }
        }
    }

    protected function parseScopeStartEvent(ActivityImpl $startEventActivity, Element $startEventElement, Element $parentElement, ActivityImpl $scopeActivity): void
    {
        $scopeProperties = $scopeActivity->getProperties();

        // set this as the scope's initial
        if (!$scopeProperties->contains(BpmnProperties::initialActivity())) {
            $scopeProperties->set(BpmnProperties::initialActivity(), $startEventActivity);
        } else {
            $this->addError("multiple start events not supported for subprocess", $parentElement, $startEventActivity->getId());
        }

        $errorEventDefinition = $startEventElement->element(self::ERROR_EVENT_DEFINITION);
        $messageEventDefinition = $startEventElement->element(self::MESSAGE_EVENT_DEFINITION);
        $signalEventDefinition = $startEventElement->element(self::SIGNAL_EVENT_DEFINITION);
        $timerEventDefinition = $startEventElement->element(self::TIMER_EVENT_DEFINITION);
        $compensateEventDefinition = $startEventElement->element(self::COMPENSATE_EVENT_DEFINITION);
        $escalationEventDefinitionElement = $startEventElement->element(self::ESCALATION_EVENT_DEFINITION);
        $conditionalEventDefinitionElement = $startEventElement->element(self::CONDITIONAL_EVENT_DEFINITION);

        if ($scopeActivity->isTriggeredByEvent()) {
            // event subprocess
            $behavior = new EventSubProcessStartEventActivityBehavior();

            // parse isInterrupting
            $isInterruptingAttr = $startEventElement->attribute(self::INTERRUPTING, "true");
            $isInterrupting = $isInterruptingAttr !== null && strtolower($isInterruptingAttr) === "true";

            if ($isInterrupting) {
                $scopeActivity->setActivityStartBehavior(ActivityStartBehavior::INTERRUPT_EVENT_SCOPE);
            } else {
                $scopeActivity->setActivityStartBehavior(ActivityStartBehavior::CONCURRENT_IN_FLOW_SCOPE);
            }

            // the event scope of the start event is the flow scope of the event subprocess
            $startEventActivity->setEventScope($scopeActivity->getFlowScope());

            if ($errorEventDefinition !== null) {
                if (!$isInterrupting) {
                    $this->addError("error start event of event subprocess must be interrupting", $startEventElement);
                }
                $this->parseErrorStartEventDefinition($errorEventDefinition, $startEventActivity);
            } elseif ($messageEventDefinition !== null) {
                $startEventActivity->getProperties()->set(BpmnProperties::type(), ActivityTypes::START_EVENT_MESSAGE);

                $messageStartEventSubscriptionDeclaration =
                    $this->parseMessageEventDefinition($messageEventDefinition, $startEventActivity->getId());
                $this->parseEventDefinitionForSubprocess($messageStartEventSubscriptionDeclaration, $startEventActivity, $messageEventDefinition);
            } elseif ($signalEventDefinition !== null) {
                $startEventActivity->getProperties()->set(BpmnProperties::type(), ActivityTypes::START_EVENT_SIGNAL);

                $eventSubscriptionDeclaration = $this->parseSignalEventDefinition($signalEventDefinition, false, $startEventActivity->getId());
                $this->parseEventDefinitionForSubprocess($eventSubscriptionDeclaration, $startEventActivity, $signalEventDefinition);
            } elseif ($timerEventDefinition !== null) {
                $this->parseTimerStartEventDefinitionForEventSubprocess($timerEventDefinition, $startEventActivity, $isInterrupting);
            } elseif ($compensateEventDefinition !== null) {
                $this->parseCompensationEventSubprocess($startEventActivity, $startEventElement, $scopeActivity, $compensateEventDefinition);
            } elseif ($escalationEventDefinitionElement !== null) {
                $startEventActivity->getProperties()->set(BpmnProperties::type(), ActivityTypes::START_EVENT_ESCALATION);

                $escalationEventDefinition = $this->createEscalationEventDefinitionForEscalationHandler($escalationEventDefinitionElement, $scopeActivity, $isInterrupting, $startEventActivity->getId());
                $this->addEscalationEventDefinition($startEventActivity->getEventScope(), $escalationEventDefinition, $escalationEventDefinitionElement, $startEventActivity->getId());
            } elseif ($conditionalEventDefinitionElement !== null) {
                $conditionalEventDef = $this->parseConditionalStartEventForEventSubprocess($conditionalEventDefinitionElement, $startEventActivity, $isInterrupting);
                $behavior = new EventSubProcessStartConditionalEventActivityBehavior($conditionalEventDef);
            } else {
                $this->addError("start event of event subprocess must be of type 'error', 'message', 'timer', 'signal', 'compensation' or 'escalation'", $startEventElement);
            }

            $startEventActivity->setActivityBehavior($behavior);
        } else { // "regular" subprocess
            $conditionalEventDefinition = $startEventElement->element(self::CONDITIONAL_EVENT_DEFINITION);

            if ($conditionalEventDefinition !== null) {
                $this->addError("conditionalEventDefinition is not allowed on start event within a subprocess", $conditionalEventDefinition, $startEventActivity->getId());
            }
            if ($timerEventDefinition !== null) {
                $this->addError("timerEventDefinition is not allowed on start event within a subprocess", $timerEventDefinition, $startEventActivity->getId());
            }
            if ($escalationEventDefinitionElement !== null) {
                $this->addError("escalationEventDefinition is not allowed on start event within a subprocess", $escalationEventDefinitionElement, $startEventActivity->getId());
            }
            if ($compensateEventDefinition !== null) {
                $this->addError("compensateEventDefinition is not allowed on start event within a subprocess", $compensateEventDefinition, $startEventActivity->getId());
            }
            if ($errorEventDefinition !== null) {
                $this->addError("errorEventDefinition only allowed on start event if subprocess is an event subprocess", $errorEventDefinition, $startEventActivity->getId());
            }
            if ($messageEventDefinition !== null) {
                $this->addError("messageEventDefinition only allowed on start event if subprocess is an event subprocess", $messageEventDefinition, $startEventActivity->getId());
            }
            if ($signalEventDefinition !== null) {
                $this->addError("signalEventDefintion only allowed on start event if subprocess is an event subprocess", $signalEventDefinition, $startEventActivity->getId());
            }

            $startEventActivity->setActivityBehavior(new NoneStartEventActivityBehavior());
        }
    }

    protected function parseCompensationEventSubprocess(ActivityImpl $startEventActivity, Element $startEventElement, ActivityImpl $scopeActivity, Element $compensateEventDefinition): void
    {
        $startEventActivity->getProperties()->set(BpmnProperties::type(), ActivityTypes::START_EVENT_COMPENSATION);
        $scopeActivity->setProperty(self::PROPERTYNAME_IS_FOR_COMPENSATION, true);

        if ($scopeActivity->getFlowScope() instanceof ProcessDefinitionEntity) {
            $this->addError("event subprocess with compensation start event is only supported for embedded subprocess "
                . "(since throwing compensation through a call activity-induced process hierarchy is not supported)", $startEventElement);
        }

        $subprocess = $scopeActivity->getFlowScope();
        $compensationHandler = $subprocess->findCompensationHandler();
        if ($compensationHandler === null) {
            // add property to subprocess
            $subprocess->setProperty(self::PROPERTYNAME_COMPENSATION_HANDLER_ID, $scopeActivity->getActivityId());
        } else {
            if ($compensationHandler->isSubProcessScope()) {
                $this->addError("multiple event subprocesses with compensation start event are not supported on the same scope", $startEventElement);
            } else {
                $this->addError("compensation boundary event and event subprocess with compensation start event are not supported on the same scope", $startEventElement);
            }
        }

        $this->validateCatchCompensateEventDefinition($compensateEventDefinition, $startEventActivity->getId());
    }

    protected function parseErrorStartEventDefinition(Element $errorEventDefinition, ActivityImpl $startEventActivity): void
    {
        $startEventActivity->getProperties()->set(BpmnProperties::type(), ActivityTypes::START_EVENT_ERROR);
        $errorRef = $errorEventDefinition->attribute("errorRef");
        $error = null;
        // the error event definition executes the event subprocess activity which
        // hosts the start event
        $eventSubProcessActivity = $startEventActivity->getFlowScope()->getId();
        $definition = new ErrorEventDefinition($eventSubProcessActivity);
        if ($errorRef !== null) {
            if (array_key_exists($errorRef, $this->errorsMap)) {
                $error = $this->errorsMap[$errorRef];
            }
            $errorCode = $error === null ? $errorRef : $error->getErrorCode();
            $definition->setErrorCode($errorCode);
        }
        $definition->setPrecedence(10);
        $this->setErrorCodeVariableOnErrorEventDefinition($errorEventDefinition, $definition);
        $this->setErrorMessageVariableOnErrorEventDefinition($errorEventDefinition, $definition);
        $this->addErrorEventDefinition($definition, $startEventActivity->getEventScope());
    }

    /**
    * Sets the value for "extension:errorCodeVariable" on the passed definition if
    * it's present.
    *
    * @param errorEventDefinition
    *          the XML errorEventDefinition tag
    * @param definition
    *          the errorEventDefintion that can get the errorCodeVariable value
    */
    protected function setErrorCodeVariableOnErrorEventDefinition(Element $errorEventDefinition, ErrorEventDefinition $definition): void
    {
        $errorCodeVar = $errorEventDefinition->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, "errorCodeVariable");
        if ($errorCodeVar !== null) {
            $definition->setErrorCodeVariable($errorCodeVar);
        }
    }

    /**
    * Sets the value for "extension:errorMessageVariable" on the passed definition if
    * it's present.
    *
    * @param errorEventDefinition
    *          the XML errorEventDefinition tag
    * @param definition
    *          the errorEventDefintion that can get the errorMessageVariable value
    */
    protected function setErrorMessageVariableOnErrorEventDefinition(Element $errorEventDefinition, ErrorEventDefinition $definition): void
    {
        $errorMessageVariable = $errorEventDefinition->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, "errorMessageVariable");
        if ($errorMessageVariable !== null) {
            $definition->setErrorMessageVariable($errorMessageVariable);
        }
    }

    protected function parseMessageEventDefinition(Element $messageEventDefinition, ?string $messageElementId): ?EventSubscriptionDeclaration
    {
        $messageRef = $messageEventDefinition->attribute("messageRef");
        if ($messageRef === null) {
            $this->addError("attribute 'messageRef' is required", $messageEventDefinition, $messageElementId);
        }
        $name = $this->resolveName($messageRef);
        $messageDefinition = null;
        if (array_key_exists($name, $this->messages)) {
            $messageDefinition = $this->messages[$name];
        }
        if ($messageDefinition === null) {
            $this->addError("Invalid 'messageRef': no message with id '" . $messageRef . "' found.", $messageEventDefinition, $messageElementId);
        }
        return new EventSubscriptionDeclaration($messageDefinition->getExpression(), EventType::message());
    }

    protected function addEventSubscriptionDeclaration(EventSubscriptionDeclaration $subscription, ScopeImpl $scope, Element $element): void
    {
        if ($subscription->getEventType() == EventType::message()->name() && (!$subscription->hasEventName())) {
            $this->addError("Cannot have a message event subscription with an empty or missing name", $element, $subscription->getActivityId());
        }

        $eventDefinitions = $scope->getProperties()->get(BpmnProperties::eventSubscriptionDeclarations());

        // if this is a message event, validate that it is the only one with the provided name for this scope
        if ($this->hasMultipleMessageEventDefinitionsWithSameName($subscription, array_values($eventDefinitions))) {
            $this->addError("Cannot have more than one message event subscription with name '" . $subscription->getUnresolvedEventName() . "' for scope '" . $scope->getId() . "'", $element, $subscription->getActivityId());
        }

        // if this is a signal event, validate that it is the only one with the provided name for this scope
        if ($this->hasMultipleSignalEventDefinitionsWithSameName($subscription, array_values($eventDefinitions))) {
            $this->addError("Cannot have more than one signal event subscription with name '" . $subscription->getUnresolvedEventName() . "' for scope '" . $scope->getId() . "'", $element, $subscription->getActivityId());
        }
        // if this is a conditional event, validate that it is the only one with the provided condition
        if ($subscription->isStartEvent() && $this->hasMultipleConditionalEventDefinitionsWithSameCondition($subscription, array_values($eventDefinitions))) {
            $this->addError("Cannot have more than one conditional event subscription with the same condition '" . $subscription->getConditionAsString() . "'", $element, $subscription->getActivityId());
        }

        $scope->getProperties()->putMapEntry(BpmnProperties::eventSubscriptionDeclarations(), $subscription->getActivityId(), $subscription);
    }

    protected function hasMultipleMessageEventDefinitionsWithSameName(EventSubscriptionDeclaration $subscription, array $eventDefinitions): bool
    {
        return $this->hasMultipleEventDefinitionsWithSameName($subscription, $eventDefinitions, EventType::message()->name());
    }

    protected function hasMultipleSignalEventDefinitionsWithSameName(EventSubscriptionDeclaration $subscription, array $eventDefinitions): bool
    {
        return $this->hasMultipleEventDefinitionsWithSameName($subscription, $eventDefinitions, EventType::signal()->name());
    }

    protected function hasMultipleConditionalEventDefinitionsWithSameCondition(EventSubscriptionDeclaration $subscription, array $eventDefinitions): bool
    {
        if ($subscription->getEventType() == EventType::conditional()->name()) {
            foreach ($eventDefinitions as $eventDefinition) {
                if (
                    $eventDefinition->getEventType() == EventType::conditional()->name() && $eventDefinition->isStartEvent() == $subscription->isStartEvent()
                    && ($eventDefinition->getConditionAsString() == $subscription->getConditionAsString())
                ) {
                    return true;
                }
            }
        }
        return false;
    }

    protected function hasMultipleEventDefinitionsWithSameName(EventSubscriptionDeclaration $subscription, array $eventDefinitions, ?string $eventType): bool
    {
        if ($subscription->getEventType() == $eventType) {
            foreach ($eventDefinitions as $eventDefinition) {
                if ($eventDefinition->getEventType() == $eventType && $eventDefinition->getUnresolvedEventName() == $subscription->getUnresolvedEventName() && $eventDefinition->isStartEvent() == $subscription->isStartEvent()) {
                    return true;
                }
            }
        }
        return false;
    }

    protected function addEventSubscriptionJobDeclaration(EventSubscriptionJobDeclaration $jobDeclaration, ActivityImpl $activity, Element $element): void
    {
        $jobDeclarationsForActivity = $activity->getProperty(self::PROPERTYNAME_EVENT_SUBSCRIPTION_JOB_DECLARATION);

        if ($jobDeclarationsForActivity === null) {
            $activity->clearProperty(self::PROPERTYNAME_EVENT_SUBSCRIPTION_JOB_DECLARATION);
        }

        if ($this->activityAlreadyContainsJobDeclarationEventType($jobDeclarationsForActivity, $jobDeclaration)) {
            $this->addError("Activity contains already job declaration with type " . $jobDeclaration->getEventType(), $element, $activity->getId());
        }
        $activity->addProperty(self::PROPERTYNAME_EVENT_SUBSCRIPTION_JOB_DECLARATION, $jobDeclaration);
    }

    /**
    * Assumes that an activity has at most one declaration of a certain eventType.
    */
    protected function activityAlreadyContainsJobDeclarationEventType(
        ?array $jobDeclarationsForActivity,
        EventSubscriptionJobDeclaration $jobDeclaration = null
    ): bool {
        $jobDeclarationsForActivity ??= [];
        foreach ($jobDeclarationsForActivity as $declaration) {
            if ($declaration->getEventType() == $jobDeclaration->getEventType()) {
                return true;
            }
        }
        return false;
    }

    /**
    * Parses the activities of a certain level in the process (process,
    * subprocess or another scope).
    *
    * @param activityElements
    *          The list of activities to be parsed. This list may be filtered before.
    * @param parentElement
    *          The 'parent' element that contains the activities (process, subprocess).
    * @param scopeElement
    *          The ScopeImpl to which the activities must be added.
    */
    public function parseActivities(array $activityElements, Element $parentElement, ScopeImpl $scopeElement): void
    {
        foreach ($activityElements as $activityElement) {
            $this->parseActivity($activityElement, $parentElement, $scopeElement);
        }
    }

    protected function parseActivity(Element $activityElement, ?Element $parentElement, ScopeImpl $scopeElement): ?ActivityImpl
    {
        $activity = null;

        $isMultiInstance = false;
        $miBody = $this->parseMultiInstanceLoopCharacteristics($activityElement, $scopeElement);
        if ($miBody !== null) {
            $scopeElement = $miBody;
            $isMultiInstance = true;
        }

        $tagName = strtoupper($activityElement->getTagName());
        if ($tagName == strtoupper(ActivityTypes::GATEWAY_EXCLUSIVE)) {
            $activity = $this->parseExclusiveGateway($activityElement, $scopeElement);
        } elseif ($tagName == strtoupper(ActivityTypes::GATEWAY_INCLUSIVE)) {
            $activity = $this->parseInclusiveGateway($activityElement, $scopeElement);
        } elseif ($tagName == strtoupper(ActivityTypes::GATEWAY_PARALLEL)) {
            $activity = $this->parseParallelGateway($activityElement, $scopeElement);
        } elseif ($tagName == strtoupper(ActivityTypes::TASK_SCRIPT)) {
            $activity = $this->parseScriptTask($activityElement, $scopeElement);
        } elseif ($tagName == strtoupper(ActivityTypes::TASK_SERVICE)) {
            $activity = $this->parseServiceTask($activityElement, $scopeElement);
        } elseif ($tagName == strtoupper(ActivityTypes::TASK_BUSINESS_RULE)) {
            $activity = $this->parseBusinessRuleTask($activityElement, $scopeElement);
        } elseif ($tagName == strtoupper(ActivityTypes::TASK)) {
            $activity = $this->parseTask($activityElement, $scopeElement);
        } elseif ($tagName == strtoupper(ActivityTypes::TASK_MANUAL_TASK)) {
            $activity = $this->parseManualTask($activityElement, $scopeElement);
        } elseif ($tagName == strtoupper(ActivityTypes::TASK_USER_TASK)) {
            $activity = $this->parseUserTask($activityElement, $scopeElement);
        } elseif ($tagName == strtoupper(ActivityTypes::TASK_SEND_TASK)) {
            $activity = $this->parseSendTask($activityElement, $scopeElement);
        } elseif ($tagName == strtoupper(ActivityTypes::TASK_RECEIVE_TASK)) {
            $activity = $this->parseReceiveTask($activityElement, $scopeElement);
        } elseif ($tagName == strtoupper(ActivityTypes::SUB_PROCESS)) {
            $activity = $this->parseSubProcess($activityElement, $scopeElement);
        } elseif ($tagName == strtoupper(ActivityTypes::CALL_ACTIVITY)) {
            $activity = $this->parseCallActivity($activityElement, $scopeElement, $isMultiInstance);
        } elseif ($tagName == strtoupper(ActivityTypes::INTERMEDIATE_EVENT_THROW)) {
            $activity = $this->parseIntermediateThrowEvent($activityElement, $scopeElement);
        } elseif ($tagName == strtoupper(ActivityTypes::GATEWAY_EVENT_BASED)) {
            $activity = $this->parseEventBasedGateway($activityElement, $parentElement, $scopeElement);
        } elseif ($tagName == strtoupper(ActivityTypes::TRANSACTION)) {
            $activity = $this->parseTransaction($activityElement, $scopeElement);
        } elseif ($tagName == strtoupper(ActivityTypes::SUB_PROCESS_AD_HOC) || $tagName == strtoupper(ActivityTypes::GATEWAY_COMPLEX)) {
            $this->addWarning("Ignoring unsupported activity type", $activityElement);
        }

        if ($isMultiInstance) {
            $activity->setProperty(self::PROPERTYNAME_IS_MULTI_INSTANCE, true);
        }

        if ($activity !== null) {
            $activity->setName($activityElement->attribute("name"));
            $this->parseActivityInputOutput($activityElement, $activity);
        }

        return $activity;
    }

    public function validateActivities(array $activities): void
    {
        foreach ($activities as $activity) {
            $this->validateActivity($activity);
            // check children if it is an own scope / subprocess / ...
            if (count($activity->getActivities()) > 0) {
                $this->validateActivities($activity->getActivities());
            }
        }
    }

    protected function validateActivity(ActivityImpl $activity): void
    {
        if ($activity->getActivityBehavior() instanceof ExclusiveGatewayActivityBehavior) {
            $this->validateExclusiveGateway($activity);
        }
        $this->validateOutgoingFlows($activity);
    }

    protected function validateOutgoingFlows(ActivityImpl $activity): void
    {
        if ($activity->isAsyncAfter()) {
            foreach ($activity->getOutgoingTransitions() as $transition) {
                if ($transition->getId() === null) {
                    $this->addError(
                        "Sequence flow with sourceRef='" . $activity->getId() . "' must have an id, $activity with id '" . $activity->getId() . "' uses 'asyncAfter'.",
                        null,
                        $activity->getId()
                    );
                }
            }
        }
    }

    public function validateExclusiveGateway(ActivityImpl $activity): void
    {
        if (count($activity->getOutgoingTransitions()) == 0) {
            // TODO: double check if this is valid (I think in Activiti yes, since we
            // need start events we will need an end event as well)
            $this->addError("Exclusive Gateway '" . $activity->getId() . "' has no outgoing sequence flows.", null, $activity->getId());
        } elseif (count($activity->getOutgoingTransitions()) == 1) {
            $flow = $activity->getOutgoingTransitions()[0];
            $condition = $flow->getProperty(BpmnParse::PROPERTYNAME_CONDITION);
            if ($condition !== null) {
                $this->addError("Exclusive Gateway '" . $activity->getId() . "' has only one outgoing sequence flow ('" . $flow->getId() . "'). This is not allowed to have a condition.", null, $activity->getId(), $flow->getId());
            }
        } else {
            $defaultSequenceFlow = $activity->getProperty("default");
            $hasDefaultFlow = !empty($defaultSequenceFlow);

            $flowsWithoutCondition = [];
            foreach ($activity->getOutgoingTransitions() as $flow) {
                $condition = $flow->getProperty(BpmnParse::PROPERTYNAME_CONDITION);
                $isDefaultFlow = !empty($flow->getId()) && $flow->getId() == $defaultSequenceFlow;
                $hasConditon = $condition !== null;

                if (!$hasConditon && !$isDefaultFlow) {
                    $flowsWithoutCondition[] = $flow;
                }
                if ($hasConditon && $isDefaultFlow) {
                    $this->addError("Exclusive Gateway '" . $activity->getId() . "' has outgoing sequence flow '" . $flow->getId() . "' which is the default flow but has a condition too.", null, $activity->getId(), $flow->getId());
                }
            }
            if ($hasDefaultFlow || count($flowsWithoutCondition) > 1) {
                // if we either have a default flow (then no flows without conditions
                // are valid at all) or if we have more than one flow without condition
                // this is an error
                foreach ($flowsWithoutCondition as $flow) {
                    $this->addError("Exclusive Gateway '" . $activity->getId() . "' has outgoing sequence flow '" . $flow->getId() . "' without condition which is not the default flow.", null, $activity->getId(), $flow->getId());
                }
            } elseif (count($flowsWithoutCondition) == 1) {
                // Havinf no default and exactly one flow without condition this is
                // considered the default one now (to not break backward compatibility)
                $flow = $flowsWithoutCondition[0];
                $this->addWarning(
                    "Exclusive Gateway '" . $activity->getId() . "' has outgoing sequence flow '" . $flow->getId() . "' without condition which is not the default flow. We assume it to be the default flow, but it is bad modeling practice, better set the default flow in your gateway.",
                    null,
                    $activity->getId(),
                    $flow->getId()
                );
            }
        }
    }

    public function parseIntermediateCatchEvent(Element $intermediateEventElement, ScopeImpl $scopeElement, ?ActivityImpl $eventBasedGateway): ?ActivityImpl
    {
        $nestedActivity = $this->createActivityOnScope($intermediateEventElement, $scopeElement);

        $timerEventDefinition = $intermediateEventElement->element(self::TIMER_EVENT_DEFINITION);
        $signalEventDefinition = $intermediateEventElement->element(self::SIGNAL_EVENT_DEFINITION);
        $messageEventDefinition = $intermediateEventElement->element(self::MESSAGE_EVENT_DEFINITION);
        $linkEventDefinitionElement = $intermediateEventElement->element(self::LINK_EVENT_DEFINITION);
        $conditionalEventDefinitionElement = $intermediateEventElement->element(self::CONDITIONAL_EVENT_DEFINITION);

        // shared by all events except for link event
        $defaultCatchBehaviour = new IntermediateCatchEventActivityBehavior($eventBasedGateway !== null);

        $this->parseAsynchronousContinuationForActivity($intermediateEventElement, $nestedActivity);
        $isEventBaseGatewayPresent = $eventBasedGateway !== null;

        if ($isEventBaseGatewayPresent) {
            $nestedActivity->setEventScope($eventBasedGateway);
            $nestedActivity->setActivityStartBehavior(ActivityStartBehavior::CANCEL_EVENT_SCOPE);
        } else {
            $nestedActivity->setEventScope($nestedActivity);
            $nestedActivity->setScope(true);
        }

        $nestedActivity->setActivityBehavior($defaultCatchBehaviour);
        if ($timerEventDefinition !== null) {
            $this->parseIntermediateTimerEventDefinition($timerEventDefinition, $nestedActivity);
        } elseif ($signalEventDefinition !== null) {
            $this->parseIntermediateSignalEventDefinition($signalEventDefinition, $nestedActivity);
        } elseif ($messageEventDefinition !== null) {
            $this->parseIntermediateMessageEventDefinition($messageEventDefinition, $nestedActivity);
        } elseif ($linkEventDefinitionElement !== null) {
            if ($isEventBaseGatewayPresent) {
                $this->addError("IntermediateCatchLinkEvent is not allowed after an EventBasedGateway.", $intermediateEventElement);
            }
            $nestedActivity->setActivityBehavior(new IntermediateCatchLinkEventActivityBehavior());
            $this->parseIntermediateLinkEventCatchBehavior($intermediateEventElement, $nestedActivity, $linkEventDefinitionElement);
        } elseif ($conditionalEventDefinitionElement !== null) {
            $conditionalEvent = $this->parseIntermediateConditionalEventDefinition($conditionalEventDefinitionElement, $nestedActivity);
            $nestedActivity->setActivityBehavior(new IntermediateConditionalEventBehavior($conditionalEvent, $isEventBaseGatewayPresent));
        } else {
            $this->addError("Unsupported intermediate catch event type", $intermediateEventElement);
        }

        $this->parseExecutionListenersOnScope($intermediateEventElement, $nestedActivity);

        foreach ($this->parseListeners as $parseListener) {
            $parseListener->parseIntermediateCatchEvent($intermediateEventElement, $scopeElement, $nestedActivity);
        }

        return $nestedActivity;
    }

    protected function parseIntermediateLinkEventCatchBehavior(Element $intermediateEventElement, ActivityImpl $activity, Element $linkEventDefinitionElement): void
    {
        $activity->getProperties()->set(BpmnProperties::type(), ActivityTypes::INTERMEDIATE_EVENT_LINK);

        $linkName = $linkEventDefinitionElement->attribute("name");
        $elementName = $intermediateEventElement->attribute("name");
        $elementId = $intermediateEventElement->attribute("id");

        if (array_key_exists($linkName, $this->eventLinkTargets)) {
            $this->addError("Multiple Intermediate Catch Events with the same link event name ('" . $linkName . "') are not allowed.", $intermediateEventElement);
        } else {
            if ($linkName != $elementName) {
                // this is valid - but not a good practice (as it is really confusing
                // for the reader of the process model) - hence we log a warning
                $this->addWarning("Link Event named '" . $elementName . "' contains link event definition with name '" . $linkName . "' - it is recommended to use the same name for both.", $intermediateEventElement);
            }

            // now we remember the link in order to replace the sequence flow later on
            $this->eventLinkTargets[$linkName] = $elementId;
        }
    }

    protected function parseIntermediateMessageEventDefinition(Element $messageEventDefinition, ActivityImpl $nestedActivity): void
    {
        $nestedActivity->getProperties()->set(BpmnProperties::type(), ActivityTypes::INTERMEDIATE_EVENT_MESSAGE);

        $messageDefinition = $this->parseMessageEventDefinition($messageEventDefinition, $nestedActivity->getId());
        $messageDefinition->setActivityId($nestedActivity->getId());
        $this->addEventSubscriptionDeclaration($messageDefinition, $nestedActivity->getEventScope(), $messageEventDefinition);

        foreach ($this->parseListeners as $parseListener) {
            $parseListener->parseIntermediateMessageCatchEventDefinition($messageEventDefinition, $nestedActivity);
        }
    }

    public function parseIntermediateThrowEvent(Element $intermediateEventElement, ScopeImpl $scopeElement): ?ActivityImpl
    {
        $signalEventDefinitionElement = $intermediateEventElement->element(self::SIGNAL_EVENT_DEFINITION);
        $compensateEventDefinitionElement = $intermediateEventElement->element(self::COMPENSATE_EVENT_DEFINITION);
        $linkEventDefinitionElement = $intermediateEventElement->element(self::LINK_EVENT_DEFINITION);
        $messageEventDefinitionElement = $intermediateEventElement->element(self::MESSAGE_EVENT_DEFINITION);
        $escalationEventDefinition = $intermediateEventElement->element(self::ESCALATION_EVENT_DEFINITION);
        $elementId = $intermediateEventElement->attribute("id");

        // the link event gets a special treatment as a throwing link event (event
        // source)
        // will not create any activity instance but serves as a "redirection" to
        // the catching link
        // event (event target)
        if ($linkEventDefinitionElement !== null) {
            $linkName = $linkEventDefinitionElement->attribute("name");

            // now we remember the link in order to replace the sequence flow later on
            $this->eventLinkSources[$elementId] = $linkName;
            // and done - no activity created
            return null;
        }

        $nestedActivityImpl = $this->createActivityOnScope($intermediateEventElement, $scopeElement);
        $activityBehavior = null;

        $this->parseAsynchronousContinuationForActivity($intermediateEventElement, $nestedActivityImpl);

        $isServiceTaskLike = $this->isServiceTaskLike($messageEventDefinitionElement);

        if ($signalEventDefinitionElement !== null) {
            $nestedActivityImpl->getProperties()->set(BpmnProperties::type(), ActivityTypes::INTERMEDIATE_EVENT_SIGNAL_THROW);

            $signalDefinition = $this->parseSignalEventDefinition($signalEventDefinitionElement, true, $nestedActivityImpl->getId());
            $activityBehavior = new ThrowSignalEventActivityBehavior($signalDefinition);
        } elseif ($compensateEventDefinitionElement !== null) {
            $nestedActivityImpl->getProperties()->set(BpmnProperties::type(), ActivityTypes::INTERMEDIATE_EVENT_COMPENSATION_THROW);
            $compensateEventDefinition = $this->parseThrowCompensateEventDefinition($compensateEventDefinitionElement, $scopeElement, $elementId);
            $activityBehavior = new CompensationEventActivityBehavior($compensateEventDefinition);
            $nestedActivityImpl->setProperty(self::PROPERTYNAME_THROWS_COMPENSATION, true);
            $nestedActivityImpl->setScope(true);
        } elseif ($messageEventDefinitionElement !== null) {
            if ($isServiceTaskLike) {
                // CAM-436 same behavior as service task
                $nestedActivityImpl->getProperties()->set(BpmnProperties::type(), ActivityTypes::INTERMEDIATE_EVENT_MESSAGE_THROW);
                $this->parseServiceTaskLike(
                    $nestedActivityImpl,
                    ActivityTypes::INTERMEDIATE_EVENT_MESSAGE_THROW,
                    $messageEventDefinitionElement,
                    $intermediateEventElement,
                    $scopeElement
                );
            } else {
                // default to non behavior if no service task
                // properties have been specified
                $nestedActivityImpl->getProperties()->set(BpmnProperties::type(), ActivityTypes::INTERMEDIATE_EVENT_NONE_THROW);
                $activityBehavior = new IntermediateThrowNoneEventActivityBehavior();
            }
        } elseif ($escalationEventDefinition !== null) {
            $nestedActivityImpl->getProperties()->set(BpmnProperties::type(), ActivityTypes::INTERMEDIATE_EVENT_ESCALATION_THROW);

            $escalation = $this->findEscalationForEscalationEventDefinition($escalationEventDefinition, $nestedActivityImpl->getId());
            if ($escalation !== null && $escalation->getEscalationCode() === null) {
                $this->addError("throwing escalation event must have an 'escalationCode'", $escalationEventDefinition, $nestedActivityImpl->getId());
            }
            $activityBehavior = new ThrowEscalationEventActivityBehavior($escalation);
        } else { // None intermediate event
            $nestedActivityImpl->getProperties()->set(BpmnProperties::type(), ActivityTypes::INTERMEDIATE_EVENT_NONE_THROW);
            $activityBehavior = new IntermediateThrowNoneEventActivityBehavior();
        }

        if ($activityBehavior !== null) {
            $nestedActivityImpl->setActivityBehavior($activityBehavior);
        }

        $this->parseExecutionListenersOnScope($intermediateEventElement, $nestedActivityImpl);

        foreach ($this->parseListeners as $parseListener) {
            $parseListener->parseIntermediateThrowEvent($intermediateEventElement, $scopeElement, $nestedActivityImpl);
        }

        if ($isServiceTaskLike) {
            // activity behavior could be set by a listener (e.g. connector); thus,
            // check is after listener invocation
            $this->validateServiceTaskLike(
                $nestedActivityImpl,
                ActivityTypes::INTERMEDIATE_EVENT_MESSAGE_THROW,
                $messageEventDefinitionElement
            );
        }

        return $nestedActivityImpl;
    }

    protected function parseThrowCompensateEventDefinition(Element $compensateEventDefinitionElement, ScopeImpl $scopeElement, ?string $parentElementId): ?CompensateEventDefinition
    {
        $activityRef = $compensateEventDefinitionElement->attribute("activityRef");
        $waitForCompletion = strtolower($compensateEventDefinitionElement->attribute("waitForCompletion", "TRUE")) == "true";
        if ($activityRef !== null) {
            if ($scopeElement->findActivityAtLevelOfSubprocess($activityRef) === null) {
                $isTriggeredByEvent = $scopeElement->getProperties()->get(BpmnProperties::triggeredByEvent());
                $type = $scopeElement->getProperty(BpmnProperties::type()->getName());
                if ($isTriggeredByEvent === true && "subprocess" == strtolower($type)) {
                    $scopeElement = $scopeElement->getFlowScope();
                }
                if ($scopeElement->findActivityAtLevelOfSubprocess($activityRef) === null) {
                    $scopeId = $scopeElement->getId();
                    $scope = $this;
                    $scopeElement->addToBacklog($activityRef, new class ($scope, $activityRef, $scopeId, $compensateEventDefinitionElement, $parentElementId) implements BacklogErrorCallbackInterface {
                        private $scope;
                        private $activityRef;
                        private $scopeId;
                        private $compensateEventDefinitionElement;
                        private $parentElementId;

                        public function __construct($scope, $activityRef, $scopeId, $compensateEventDefinitionElement, $parentElementId)
                        {
                            $this->scope = $scope;
                            $this->activityRef = $activityRef;
                            $this->scopeId = $scopeId;
                            $this->compensateEventDefinitionElement = $compensateEventDefinitionElement;
                            $this->parentElementId = $parentElementId;
                        }

                        public function callback(): void
                        {
                            $this->scope->addError(
                                "Invalid attribute value for 'activityRef': no activity with id '" . $this->activityRef . "' in scope '" . $this->scopeId . "'",
                                $this->compensateEventDefinitionElement,
                                $this->parentElementId
                            );
                        }
                    });
                }
            }
        }

        $compensateEventDefinition = new CompensateEventDefinition();
        $compensateEventDefinition->setActivityRef($activityRef);

        $compensateEventDefinition->setWaitForCompletion($waitForCompletion);
        if (!$waitForCompletion) {
            $this->addWarning(
                "Unsupported attribute value for 'waitForCompletion': 'waitForCompletion=false' is not supported. Compensation event will wait for compensation to join.",
                $compensateEventDefinitionElement,
                $parentElementId
            );
        }

        return $compensateEventDefinition;
    }

    protected function validateCatchCompensateEventDefinition(Element $compensateEventDefinitionElement, ?string $parentElementId): void
    {
        $activityRef = $compensateEventDefinitionElement->attribute("activityRef");
        if ($activityRef !== null) {
            $this->addWarning(
                "attribute 'activityRef' is not supported on catching compensation event. attribute will be ignored",
                $compensateEventDefinitionElement,
                $parentElementId
            );
        }

        $waitForCompletion = $compensateEventDefinitionElement->attribute("waitForCompletion");
        if ($waitForCompletion !== null) {
            $this->addWarning(
                "attribute 'waitForCompletion' is not supported on catching compensation event. attribute will be ignored",
                $compensateEventDefinitionElement,
                $parentElementId
            );
        }
    }

    protected function parseBoundaryCompensateEventDefinition(Element $compensateEventDefinition, ActivityImpl $activity): void
    {
        $activity->getProperties()->set(BpmnProperties::type(), ActivityTypes::BOUNDARY_COMPENSATION);

        $hostActivity = $activity->getEventScope();
        foreach ($activity->getFlowScope()->getActivities() as $sibling) {
            if ($sibling->getProperty(BpmnProperties::type()->getName()) == "compensationBoundaryCatch" && $sibling->getEventScope() == $hostActivity && $sibling != $activity) {
                $this->addError("multiple boundary events with compensateEventDefinition not supported on same activity", $compensateEventDefinition, $activity->getId());
            }
        }

        $this->validateCatchCompensateEventDefinition($compensateEventDefinition, $activity->getId());
    }

    protected function parseBoundaryCancelEventDefinition(Element $cancelEventDefinition, ActivityImpl $activity): ?ActivityBehavior
    {
        $activity->getProperties()->set(BpmnProperties::type(), ActivityTypes::BOUNDARY_CANCEL);

        LegacyBehavior::parseCancelBoundaryEvent($activity);

        $transaction = $activity->getEventScope();
        if ($transaction->getActivityBehavior() !== null && $transaction->getActivityBehavior() instanceof MultiInstanceActivityBehavior) {
            $transaction = $transaction->getActivities()->get(0);
        }

        if ("transaction" != $transaction->getProperty(BpmnProperties::type()->getName())) {
            $this->addError("boundary event with cancelEventDefinition only supported on transaction subprocesses", $cancelEventDefinition, $activity->getId());
        }

        // ensure there is only one cancel boundary event
        foreach ($activity->getFlowScope()->getActivities() as $sibling) {
            if ("cancelBoundaryCatch" == $sibling->getProperty(BpmnProperties::type()->getName()) && $sibling != $activity && $sibling->getEventScope() == $transaction) {
                $this->addError("multiple boundary events with cancelEventDefinition not supported on same transaction subprocess", $cancelEventDefinition, $activity->getId());
            }
        }

        // find all cancel end events
        foreach ($transaction->getActivities() as $childActivity) {
            $activityBehavior = $childActivity->getActivityBehavior();
            if ($activityBehavior !== null && $activityBehavior instanceof CancelEndEventActivityBehavior) {
                $activityBehavior->setCancelBoundaryEvent($activity);
            }
        }

        return new CancelBoundaryEventActivityBehavior();
    }

    /**
    * Parses loopCharacteristics (standardLoop/Multi-instance) of an activity, if
    * any is defined.
    */
    public function parseMultiInstanceLoopCharacteristics(Element $activityElement, ScopeImpl $scope): ?ScopeImpl
    {
        $miLoopCharacteristics = $activityElement->element("multiInstanceLoopCharacteristics");
        if ($miLoopCharacteristics === null) {
            return null;
        } else {
            $id = $activityElement->attribute("id");

            //LOG.parsingElement("mi body for activity", id);

            $id = self::getIdForMiBody($id);
            $miBodyScope = $scope->createActivity($id);
            $this->setActivityAsyncDelegates($miBodyScope);
            $miBodyScope->setProperty(BpmnProperties::type()->getName(), ActivityTypes::MULTI_INSTANCE_BODY);
            $miBodyScope->setScope(true);

            $isSequential = $this->parseBooleanAttribute($miLoopCharacteristics->attribute("isSequential"), false);

            $behavior = null;
            if ($isSequential) {
                $behavior = new SequentialMultiInstanceActivityBehavior();
            } else {
                $behavior = new ParallelMultiInstanceActivityBehavior();
            }
            $miBodyScope->setActivityBehavior($behavior);

            // loopCardinality
            $loopCardinality = $miLoopCharacteristics->element("loopCardinality");
            if ($loopCardinality !== null) {
                $loopCardinalityText = $loopCardinality->getText();
                if ($loopCardinalityText === null || "" == $loopCardinalityText) {
                    $this->addError("loopCardinality must be defined for a multiInstanceLoopCharacteristics definition ", $miLoopCharacteristics, $id);
                }
                $behavior->setLoopCardinalityExpression($this->expressionManager->createExpression($loopCardinalityText));
            }

            // completionCondition
            $completionCondition = $miLoopCharacteristics->element("completionCondition");
            if ($completionCondition !== null) {
                $completionConditionText = $completionCondition->getText();
                $behavior->setCompletionConditionExpression($this->expressionManager->createExpression($completionConditionText));
            }

            // activiti:collection
            $collection = $miLoopCharacteristics->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, "collection");
            if (!empty($collection)) {
                if (strpos($collection, "{") !== false) {
                    $behavior->setCollectionExpression($this->expressionManager->createExpression($collection));
                } else {
                    $behavior->setCollectionVariable($collection);
                }
            }

            // loopDataInputRef
            $loopDataInputRef = $miLoopCharacteristics->element("loopDataInputRef");
            if ($loopDataInputRef !== null) {
                $loopDataInputRefText = $loopDataInputRef->getText();
                if ($loopDataInputRefText !== null) {
                    if (strpos($loopDataInputRefText, "{") !== false) {
                        $behavior->setCollectionExpression($this->expressionManager->createExpression($loopDataInputRefText));
                    } else {
                        $behavior->setCollectionVariable($loopDataInputRefText);
                    }
                }
            }

            // activiti:elementVariable
            $elementVariable = $miLoopCharacteristics->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, "elementVariable");
            if ($elementVariable !== null) {
                $behavior->setCollectionElementVariable($elementVariable);
            }

            // dataInputItem
            $inputDataItem = $miLoopCharacteristics->element("inputDataItem");
            if ($inputDataItem !== null) {
                $inputDataItemName = $inputDataItem->attribute("name");
                $behavior->setCollectionElementVariable($inputDataItemName);
            }

            // Validation
            if ($behavior->getLoopCardinalityExpression() === null && $behavior->getCollectionExpression() === null && $behavior->getCollectionVariable() === null) {
                $this->addError("Either loopCardinality or loopDataInputRef/activiti:collection must been set", $miLoopCharacteristics, $id);
            }

            // Validation
            if ($behavior->getCollectionExpression() === null && $behavior->getCollectionVariable() === null && $behavior->getCollectionElementVariable() !== null) {
                $this->addError("LoopDataInputRef/activiti:collection must be set when using inputDataItem or activiti:elementVariable", $miLoopCharacteristics, $id);
            }

            foreach ($this->parseListeners as $parseListener) {
                $parseListener->parseMultiInstanceLoopCharacteristics($activityElement, $miLoopCharacteristics, $miBodyScope);
            }

            return $miBodyScope;
        }
    }

    public static function getIdForMiBody($id): ?string
    {
        return $id . self::MULTI_INSTANCE_BODY_ID_SUFFIX;
    }

    /**
    * Parses the generic information of an activity element ($id, name,
    * documentation, etc.), and creates a new ActivityImpl on the given
    * scope element.
    */
    public function createActivityOnScope(Element $activityElement, ScopeImpl $scopeElement): ActivityImpl
    {
        $id = $activityElement->attribute("id");

        //LOG.parsingElement("activity", id);
        $activity = $scopeElement->createActivity($id);

        $activity->setProperty("name", $activityElement->attribute("name"));
        $activity->setProperty("documentation", self::parseDocumentation($activityElement));
        $activity->setProperty("default", $activityElement->attribute("default"));
        $activity->getProperties()->set(BpmnProperties::type(), $activityElement->getTagName());
        $activity->setProperty("line", $activityElement->getLine());
        $this->setActivityAsyncDelegates($activity);
        $activity->setProperty(self::PROPERTYNAME_JOB_PRIORITY, $this->parsePriority($activityElement, self::PROPERTYNAME_JOB_PRIORITY));

        if ($this->isCompensationHandler($activityElement)) {
            $activity->setProperty(self::PROPERTYNAME_IS_FOR_COMPENSATION, true);
        }

        return $activity;
    }

    /**
    * Sets the delegates for the activity, which will be called
    * if the attribute asyncAfter or asyncBefore was changed.
    *
    * @param activity the activity which gets the delegates
    */
    protected function setActivityAsyncDelegates(ActivityImpl $activity): void
    {
        $scope = $this;
        $activity->setDelegateAsyncAfterUpdate(new class ($scope, $activity) implements AsyncAfterUpdateInterface {
            private $scope;
            private $activity;

            public function __construct($scope, $activity)
            {
                $this->scope = $scope;
                $this->activity = $activity;
            }

            public function updateAsyncAfter(bool $asyncAfter, bool $exclusive): void
            {
                if ($asyncAfter) {
                    $this->scope->addMessageJobDeclaration(new AsyncAfterMessageJobDeclaration(), $this->activity, $exclusive);
                } else {
                    $this->scope->removeMessageJobDeclarationWithJobConfiguration($this->activity, MessageJobDeclaration::ASYNC_AFTER);
                }
            }
        });

        $activity->setDelegateAsyncBeforeUpdate(new class ($scope, $activity) implements AsyncBeforeUpdateInterface {
            private $scope;
            private $activity;

            public function __construct($scope, $activity)
            {
                $this->scope = $scope;
                $this->activity = $activity;
            }

            public function updateAsyncBefore(bool $asyncBefore, bool $exclusive): void
            {
                if ($asyncBefore) {
                    $this->scope->addMessageJobDeclaration(new AsyncBeforeMessageJobDeclaration(), $this->activity, $exclusive);
                } else {
                    $this->scope->removeMessageJobDeclarationWithJobConfiguration($this->activity, MessageJobDeclaration::ASYNC_BEFORE);
                }
            }
        });
    }

    /**
    * Adds the new message job declaration to existing declarations.
    * There will be executed an existing check before the adding is executed.
    *
    * @param messageJobDeclaration the new message job declaration
    * @param activity the corresponding activity
    * @param exclusive the flag which indicates if the async should be exclusive
    */
    public function addMessageJobDeclaration(MessageJobDeclaration $messageJobDeclaration, ActivityImpl $activity, bool $exclusive): void
    {
        $procDef = $activity->getProcessDefinition();
        if (!$this->exists($messageJobDeclaration, $procDef->getKey(), $activity->getActivityId())) {
            $messageJobDeclaration->setExclusive($exclusive);
            $messageJobDeclaration->setActivity($activity);
            $messageJobDeclaration->setJobPriorityProvider($activity->getProperty(self::PROPERTYNAME_JOB_PRIORITY));

            $this->addMessageJobDeclarationToActivity($messageJobDeclaration, $activity);
            $this->addJobDeclarationToProcessDefinition($messageJobDeclaration, $procDef);
        }
    }

    /**
    * Checks whether the message declaration already exists.
    *
    * @param msgJobdecl the message job declaration which is searched
    * @param procDefKey the corresponding process definition key
    * @param activityId the corresponding activity id
    * @return bool true if the message job declaration exists, false otherwise
    */
    protected function exists(MessageJobDeclaration $msgJobdecl, ?string $procDefKey, ?string $activityId): bool
    {
        $exist = false;
        if (array_key_exists($procDefKey, $this->jobDeclarations)) {
            $declarations = $this->jobDeclarations[$procDefKey];
            for ($i = 0; $i < count($declarations) && !$exist; $i += 1) {
                $decl = $declarations[$i];
                if (
                    $decl->getActivityId() == $activityId &&
                    strtolower($decl->getJobConfiguration()) == strtolower($msgJobdecl->getJobConfiguration())
                ) {
                    $exist = true;
                }
            }
        }
        return $exist;
    }

    /**
    * Removes a job declaration which belongs to the given activity and has the given job configuration.
    *
    * @param activity the activity of the job declaration
    * @param jobConfiguration  the job configuration of the declaration
    */
    public function removeMessageJobDeclarationWithJobConfiguration(ActivityImpl $activity, ?string $jobConfiguration): void
    {
        $messageJobDeclarations = $activity->getProperty(self::PROPERTYNAME_MESSAGE_JOB_DECLARATION);
        if (!empty($messageJobDeclarations)) {
            foreach ($messageJobDeclarations as $key => $msgDecl) {
                if (
                    strtolower($msgDecl->getJobConfiguration()) == strtolower($jobConfiguration)
                    && strtolower($msgDecl->getActivityId()) == strtolower($activity->getActivityId())
                ) {
                    $activity->clearPropertyItem(self::PROPERTYNAME_MESSAGE_JOB_DECLARATION, $key);
                }
            }
        }

        $procDef = $activity->getProcessDefinition();
        if (array_key_exists($procDef->getKey(), $this->jobDeclarations)) {
            $declarations = $this->jobDeclarations[$procDef->getKey()];
            foreach ($declarations as $key => $jobDcl) {
                if (
                    strtolower($jobDcl->getJobConfiguration()) == strtolower($jobConfiguration)
                    && strtolower($jobDcl->getActivityId()) == strtolower($activity->getActivityId())
                ) {
                    unset($this->jobDeclarations[$procDef->getKey()][$key]);
                }
            }
        }
    }

    public static function parseDocumentation($data): ?string
    {
        if (is_array($data)) {
            if (empty($data)) {
                return null;
            }
            $builder = "";
            foreach ($data as $e) {
                if (strlen($builder) != 0) {
                    $builder .= "\n\n";
                }

                $builder .= trim($e);
            }
            return $builder;
        } elseif ($data instanceof Element) {
            $docElements = $data->elements("documentation");
            $docStrings = [];
            foreach ($docElements as $e) {
                $docStrings[] = $e->getText();
            }
            return self::parseDocumentation($docStrings);
        }
    }

    protected function isCompensationHandler(Element $activityElement): bool
    {
        $isForCompensation = $activityElement->attribute("isForCompensation");
        return $isForCompensation !== null && strtolower($isForCompensation) === "true";
    }

    /**
    * Parses an exclusive gateway declaration.
    */
    public function parseExclusiveGateway(Element $exclusiveGwElement, ScopeImpl $scope): ?ActivityImpl
    {
        $activity = $this->createActivityOnScope($exclusiveGwElement, $scope);
        $activity->setActivityBehavior(new ExclusiveGatewayActivityBehavior());

        $this->parseAsynchronousContinuationForActivity($exclusiveGwElement, $activity);

        $this->parseExecutionListenersOnScope($exclusiveGwElement, $activity);

        foreach ($this->parseListeners as $parseListener) {
            $parseListener->parseExclusiveGateway($exclusiveGwElement, $scope, $activity);
        }
        return $activity;
    }

    /**
    * Parses an inclusive gateway declaration.
    */
    public function parseInclusiveGateway(Element $inclusiveGwElement, ScopeImpl $scope): ?ActivityImpl
    {
        $activity = $this->createActivityOnScope($inclusiveGwElement, $scope);
        $activity->setActivityBehavior(new InclusiveGatewayActivityBehavior());

        $this->parseAsynchronousContinuationForActivity($inclusiveGwElement, $activity);

        $this->parseExecutionListenersOnScope($inclusiveGwElement, $activity);

        foreach ($this->parseListeners as $parseListener) {
            $parseListener->parseInclusiveGateway($inclusiveGwElement, $scope, $activity);
        }
        return $activity;
    }

    public function parseEventBasedGateway(Element $eventBasedGwElement, Element $parentElement, ScopeImpl $scope): ?ActivityImpl
    {
        $activity = $this->createActivityOnScope($eventBasedGwElement, $scope);
        $activity->setActivityBehavior(new EventBasedGatewayActivityBehavior());
        $activity->setScope(true);

        $this->parseAsynchronousContinuationForActivity($eventBasedGwElement, $activity);

        if ($activity->isAsyncAfter()) {
            $this->addError("'asyncAfter' not supported for " . $eventBasedGwElement->getTagName() . " elements.", $eventBasedGwElement);
        }

        $this->parseExecutionListenersOnScope($eventBasedGwElement, $activity);

        foreach ($this->parseListeners as $parseListener) {
            $parseListener->parseEventBasedGateway($eventBasedGwElement, $scope, $activity);
        }

        // find all outgoing sequence flows:
        $sequenceFlows = $parentElement->elements("sequenceFlow");

        // collect all siblings in a map
        $siblingsMap = [];
        $siblings = $parentElement->elements();
        foreach ($siblings as $sibling) {
            $siblingsMap[$sibling->attribute("id")] = $sibling;
        }

        foreach ($sequenceFlows as $sequenceFlow) {
            $sourceRef = $sequenceFlow->attribute("sourceRef");
            $targetRef = $sequenceFlow->attribute("targetRef");

            if ($activity->getId() == $sourceRef) {
                if (array_key_exists($targetRef, $siblingsMap)) {
                    $sibling = $siblingsMap[$targetRef];
                    if (strtoupper($sibling->getTagName()) == strtoupper(ActivityTypes::INTERMEDIATE_EVENT_CATCH)) {
                        $catchEventActivity = $this->parseIntermediateCatchEvent($sibling, $scope, $activity);

                        if ($catchEventActivity !== null) {
                            $this->parseActivityInputOutput($sibling, $catchEventActivity);
                        }
                    } else {
                        $this->addError("Event based gateway can only be connected to elements of type intermediateCatchEvent", $eventBasedGwElement);
                    }
                }
            }
        }

        return $activity;
    }

    /**
    * Parses a parallel gateway declaration.
    */
    public function parseParallelGateway(Element $parallelGwElement, ScopeImpl $scope): ?ActivityImpl
    {
        $activity = $this->createActivityOnScope($parallelGwElement, $scope);
        $activity->setActivityBehavior(new ParallelGatewayActivityBehavior());

        $this->parseAsynchronousContinuationForActivity($parallelGwElement, $activity);

        $this->parseExecutionListenersOnScope($parallelGwElement, $activity);

        foreach ($this->parseListeners as $parseListener) {
            $parseListener->parseParallelGateway($parallelGwElement, $scope, $activity);
        }
        return $activity;
    }

    /**
    * Parses a scriptTask declaration.
    */
    public function parseScriptTask(Element $scriptTaskElement, ScopeImpl $scope): ?ActivityImpl
    {
        $activity = $this->createActivityOnScope($scriptTaskElement, $scope);

        $activityBehavior = $this->parseScriptTaskElement($scriptTaskElement);

        if ($activityBehavior !== null) {
            $this->parseAsynchronousContinuationForActivity($scriptTaskElement, $activity);

            $activity->setActivityBehavior($activityBehavior);

            $this->parseExecutionListenersOnScope($scriptTaskElement, $activity);

            foreach ($this->parseListeners as $parseListener) {
                $parseListener->parseScriptTask($scriptTaskElement, $scope, $activity);
            }
        }

        return $activity;
    }

    /**
    * Returns a ScriptTaskActivityBehavior for the script task element
    * corresponding to the script source or resource specified.
    *
    * @param scriptTaskElement
    *          the script task element
    * @return ScriptTaskActivityBehavior the corresponding ScriptTaskActivityBehavior
    */
    protected function parseScriptTaskElement(Element $scriptTaskElement): ?ScriptTaskActivityBehavior
    {
        // determine script language
        $language = $scriptTaskElement->attribute("scriptFormat");
        if ($language === null) {
            $language = ScriptingEngines::DEFAULT_SCRIPTING_LANGUAGE;
        }
        $resultVariableName = $this->parseResultVariable($scriptTaskElement);

        // determine script source
        $scriptSource = null;
        $scriptElement = $scriptTaskElement->element("script");
        if ($scriptElement !== null) {
            $scriptSource = $scriptElement->getText();
        }
        $scriptResource = $scriptTaskElement->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, self::PROPERTYNAME_RESOURCE);

        try {
            $script = ScriptUtil::getScript($language, $scriptSource, $scriptResource, $this->expressionManager);
            return new ScriptTaskActivityBehavior($script, $resultVariableName);
        } catch (ProcessEngineException $e) {
            $this->addError("Unable to process ScriptTask: " . $e->getMessage(), $scriptElement);
            return null;
        }
    }

    protected function parseResultVariable(Element $element): ?string
    {
        // determine if result variable exists
        $resultVariableName = $element->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, "resultVariable");
        if ($resultVariableName === null) {
            // for backwards compatible reasons
            $resultVariableName = $element->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, "resultVariableName");
        }
        return $resultVariableName;
    }

    /**
    * Parses a serviceTask declaration.
    */
    public function parseServiceTask(Element $serviceTaskElement, ScopeImpl $scope): ?ActivityImpl
    {
        $activity = $this->createActivityOnScope($serviceTaskElement, $scope);

        $this->parseAsynchronousContinuationForActivity($serviceTaskElement, $activity);

        $elementName = "serviceTask";
        $this->parseServiceTaskLike($activity, $elementName, $serviceTaskElement, $serviceTaskElement, $scope);

        $this->parseExecutionListenersOnScope($serviceTaskElement, $activity);

        foreach ($this->parseListeners as $parseListener) {
            $parseListener->parseServiceTask($serviceTaskElement, $scope, $activity);
        }

        // activity behavior could be set by a listener (e.g. connector); thus,
        // check is after listener invocation
        $this->validateServiceTaskLike($activity, $elementName, $serviceTaskElement);

        return $activity;
    }

    /**
    * @param elementName
    * @param serviceTaskElement the element that contains the jabe service task definition
    *   (e.g. extension:class attributes)
    * @param propertiesElement the element that contains the extension:properties extension elements
    *   that apply to this service task. Usually, but not always, this is the same as serviceTaskElement
    * @param scope
    * @return
    */
    public function parseServiceTaskLike(
        ActivityImpl $activity,
        ?string $elementName,
        Element $serviceTaskElement,
        Element $propertiesElement,
        ScopeImpl $scope
    ): void {
        $type = $serviceTaskElement->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, self::TYPE);
        $className = $serviceTaskElement->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, self::PROPERTYNAME_CLASS);
        $expression = $serviceTaskElement->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, self::PROPERTYNAME_EXPRESSION);
        $delegateExpression = $serviceTaskElement->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, self::PROPERTYNAME_DELEGATE_EXPRESSION);
        $resultVariableName = $this->parseResultVariable($serviceTaskElement);
        if (!empty($type)) {
            if (strtolower($type) == "mail") {
                $this->parseEmailServiceTask($activity, $serviceTaskElement, $this->parseFieldDeclarations($serviceTaskElement));
            } elseif (strtolower($type) == "shell") {
                $this->parseShellServiceTask($activity, $serviceTaskElement, $this->parseFieldDeclarations($serviceTaskElement));
            } elseif (strtolower($type) == "external") {
                $this->parseExternalServiceTask($activity, $serviceTaskElement, $propertiesElement);
            } else {
                $this->addError("Invalid usage of type attribute on " . $elementName . ": '" . $type . "'", $serviceTaskElement);
            }
        } elseif (!empty($className)) {
            if ($resultVariableName !== null) {
                $this->addError("'resultVariableName' not supported for " . $elementName . " elements using 'class'", $serviceTaskElement);
            }
            $activity->setActivityBehavior(new ClassDelegateActivityBehavior($className, $this->parseFieldDeclarations($serviceTaskElement)));
        } elseif (!empty($delegateExpression)) {
            if ($resultVariableName !== null) {
                $this->addError("'resultVariableName' not supported for " . $elementName . " elements using 'delegateExpression'", $serviceTaskElement);
            }
            $activity->setActivityBehavior(
                new ServiceTaskDelegateExpressionActivityBehavior(
                    $this->expressionManager->createExpression($delegateExpression),
                    $this->parseFieldDeclarations($serviceTaskElement)
                )
            );
        } elseif (!empty($expression)) {
            $activity->setActivityBehavior(new ServiceTaskExpressionActivityBehavior($this->expressionManager->createExpression($expression), $resultVariableName));
        }
    }

    protected function validateServiceTaskLike(
        ActivityImpl $activity,
        ?string $elementName,
        Element $serviceTaskElement
    ): void {
        if ($activity->getActivityBehavior() === null) {
            $this->addError(
                "One of the attributes 'class', 'delegateExpression', 'type', "
                . "or 'expression' is mandatory on " . $elementName . ". If you are using a connector, make sure the"
                . "connect process engine plugin is registered with the process engine.",
                $serviceTaskElement
            );
        }
    }

    /**
    * Parses a businessRuleTask declaration.
    */
    public function parseBusinessRuleTask(Element $businessRuleTaskElement, ScopeImpl $scope): ?ActivityImpl
    {
        $decisionRef = $businessRuleTaskElement->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, "decisionRef");
        if ($decisionRef !== null) {
            return null;
            //return parseDmnBusinessRuleTask(businessRuleTaskElement, $scope);
        } else {
            $activity = $this->createActivityOnScope($businessRuleTaskElement, $scope);
            $this->parseAsynchronousContinuationForActivity($businessRuleTaskElement, $activity);

            $elementName = "businessRuleTask";
            $this->parseServiceTaskLike(
                $activity,
                $elementName,
                $businessRuleTaskElement,
                $businessRuleTaskElement,
                $scope
            );

            $this->parseExecutionListenersOnScope($businessRuleTaskElement, $activity);

            foreach ($this->parseListeners as $parseListener) {
                $parseListener->parseBusinessRuleTask($businessRuleTaskElement, $scope, $activity);
            }

            // activity behavior could be set by a listener (e.g. connector); thus,
            // check is after listener invocation
            $this->validateServiceTaskLike(
                $activity,
                $elementName,
                $businessRuleTaskElement
            );
            return $activity;
        }
    }

    /**
    * Parse a Business Rule Task which references a decision.
    */
    /*protected ActivityImpl parseDmnBusinessRuleTask(Element businessRuleTaskElement, ScopeImpl $scope) {
        ActivityImpl activity = $this->createActivityOnScope(businessRuleTaskElement, $scope);
        // the activity is a scope since the result variable is stored as local variable
        $activity->setScope(true);

        $this->parseAsynchronousContinuationForActivity(businessRuleTaskElement, $activity);

        String decisionRef = businessRuleTaskElement->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, "decisionRef");

        BaseCallableElement callableElement = new BaseCallableElement();
        callableElement->setDeploymentId(deployment->getId());

        ParameterValueProvider definitionKeyProvider = $this->createParameterValueProvider(decisionRef, expressionManager);
        callableElement->setDefinitionKeyValueProvider(definitionKeyProvider);

        $this->parseBinding(businessRuleTaskElement, $activity, callableElement, "decisionRefBinding");
        $this->parseVersion(businessRuleTaskElement, $activity, callableElement, "decisionRefBinding", "decisionRefVersion");
        $this->parseVersionTag(businessRuleTaskElement, $activity, callableElement, "decisionRefBinding", "decisionRefVersionTag");
        $this->parseTenantId(businessRuleTaskElement, $activity, callableElement, "decisionRefTenantId");

        String resultVariable = $this->parseResultVariable(businessRuleTaskElement);
        DecisionResultMapper decisionResultMapper = $this->parseDecisionResultMapper(businessRuleTaskElement);

        DmnBusinessRuleTaskActivityBehavior behavior = new DmnBusinessRuleTaskActivityBehavior(callableElement, resultVariable, decisionResultMapper);
        $activity->setActivityBehavior(behavior);

        $this->parseExecutionListenersOnScope(businessRuleTaskElement, $activity);

        for (BpmnParseListener parseListener : parseListeners) {
            $parseListener->parseBusinessRuleTask(businessRuleTaskElement, $scope, $activity);
        }

        return $activity;
    }*/

    /*protected DecisionResultMapper parseDecisionResultMapper(Element businessRuleTaskElement) {
        // default mapper is 'resultList'
        String decisionResultMapper = businessRuleTaskElement->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, "mapDecisionResult");
        DecisionResultMapper mapper = DecisionEvaluationUtil->getDecisionResultMapperForName(decisionResultMapper);

        if (mapper === null) {
            $this->addError("No decision result mapper found for name '" .decisionResultMapper
                . "'. Supported mappers are 'singleEntry', 'singleResult', 'collectEntries' and 'resultList'.", businessRuleTaskElement);
        }

        return mapper;
    }*/

    /**
    * Parse async continuation of an activity and create async jobs for the activity.
    * <br/> <br/>
    * When the activity is marked as multi instance, then async jobs create instead for the multi instance body.
    * When the wrapped activity has async characteristics in 'multiInstanceLoopCharacteristics' element,
    * then async jobs create additionally for the wrapped activity.
    */
    protected function parseAsynchronousContinuationForActivity(Element $activityElement, ActivityImpl $activity): void
    {
        // can't use #getMultiInstanceScope here to determine whether the task is multi-instance,
        // since the property hasn't been set yet (cf parseActivity)
        $parentFlowScopeActivity = $activity->getParentFlowScopeActivity();
        if (
            $parentFlowScopeActivity !== null
            && $parentFlowScopeActivity->getActivityBehavior() instanceof MultiInstanceActivityBehavior
            && !$activity->isCompensationHandler()
        ) {
            $this->parseAsynchronousContinuation($activityElement, $parentFlowScopeActivity);

            $miLoopCharacteristics = $activityElement->element("multiInstanceLoopCharacteristics");
            $this->parseAsynchronousContinuation($miLoopCharacteristics, $activity);
        } else {
            $this->parseAsynchronousContinuation($activityElement, $activity);
        }
    }

    /**
    * Parse async continuation of the given element and create async jobs for the activity.
    *
    * @param element with async characteristics
    * @param activity
    */
    protected function parseAsynchronousContinuation(Element $element, ActivityImpl $activity): void
    {
        $isAsyncBefore = $this->isAsyncBefore($element);
        $isAsyncAfter = $this->isAsyncAfter($element);
        $exclusive = $this->isExclusive($element);

        // set properties on activity
        $activity->setAsyncBefore($isAsyncBefore, $exclusive);
        $activity->setAsyncAfter($isAsyncAfter, $exclusive);
    }

    protected function parsePriority(Element $element, ?string $priorityAttribute): ?ParameterValueProviderInterface
    {
        $priorityAttributeValue = $element->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, $priorityAttribute);

        if ($priorityAttributeValue === null) {
            return null;
        } else {
            $value = $priorityAttributeValue;
            if (!StringUtil::isExpression($priorityAttributeValue)) {
                // constant values must be valid integers
                try {
                    $value = intval($priorityAttributeValue);
                } catch (\Exception $e) {
                    $this->addError("Value '" . $priorityAttributeValue . "' for attribute '" . $priorityAttribute . "' is not a valid number", $element);
                }
            }
            return $this->createParameterValueProvider($value, $this->expressionManager);
        }
    }

    protected function parseTopic(Element $element, ?string $topicAttribute): ?ParameterValueProviderInterface
    {
        $topicAttributeValue = $element->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, $topicAttribute);

        if ($topicAttributeValue === null) {
            $this->addError("External tasks must specify a 'topic' attribute", $element);
            return null;
        } else {
            return $this->createParameterValueProvider($topicAttributeValue, $this->expressionManager);
        }
    }

    protected function addMessageJobDeclarationToActivity(MessageJobDeclaration $messageJobDeclaration, ActivityImpl $activity): void
    {
        $messageJobDeclarations = $activity->getProperty(self::PROPERTYNAME_MESSAGE_JOB_DECLARATION);
        if (empty($messageJobDeclarations)) {
            $activity->clearProperty(self::PROPERTYNAME_MESSAGE_JOB_DECLARATION);
        }
        $activity->addProperty(self::PROPERTYNAME_MESSAGE_JOB_DECLARATION, $messageJobDeclaration);
    }

    protected function addJobDeclarationToProcessDefinition(JobDeclaration $jobDeclaration, ProcessDefinitionInterface $processDefinition): void
    {
        $key = $processDefinition->getKey();

        if (!array_key_exists($key, $this->jobDeclarations)) {
            $this->jobDeclarations[$key] = [];
        }
        $this->jobDeclarations[$key][] = $jobDeclaration;
    }

    /**
    * Parses a sendTask declaration.
    */
    public function parseSendTask(Element $sendTaskElement, ScopeImpl $scope): ?ActivityImpl
    {
        $activity = $this->createActivityOnScope($sendTaskElement, $scope);

        if ($this->isServiceTaskLike($sendTaskElement)) {
            // CAM-942: If expression or class is set on a SendTask it behaves like a service task
            // to allow implementing the send handling yourself
            $elementName = "sendTask";
            $this->parseAsynchronousContinuationForActivity($sendTaskElement, $activity);

            $this->parseServiceTaskLike($activity, $elementName, $sendTaskElement, $sendTaskElement, $scope);

            $this->parseExecutionListenersOnScope($sendTaskElement, $activity);

            foreach ($this->parseListeners as $parseListener) {
                $parseListener->parseSendTask($sendTaskElement, $scope, $activity);
            }

            // activity behavior could be set by a listener (e.g. connector); thus,
            // check is after listener invocation
            $this->validateServiceTaskLike($activity, $elementName, $sendTaskElement);
        } else {
            $this->parseAsynchronousContinuationForActivity($sendTaskElement, $activity);
            $this->parseExecutionListenersOnScope($sendTaskElement, $activity);

            foreach ($this->parseListeners as $parseListener) {
                $parseListener->parseSendTask($sendTaskElement, $scope, $activity);
            }

            // activity behavior could be set by a listener; thus, check is after listener invocation
            if ($activity->getActivityBehavior() === null) {
                $this->addError("One of the attributes 'class', 'delegateExpression', 'type', or 'expression' is mandatory on sendTask.", $sendTaskElement);
            }
        }

        return $activity;
    }

    protected function parseEmailServiceTask(ActivityImpl $activity, Element $serviceTaskElement, array $fieldDeclarations): void
    {
        $this->validateFieldDeclarationsForEmail($serviceTaskElement, $fieldDeclarations);
        $activity->setActivityBehavior(ClassDelegateUtil::instantiateDelegate(MailActivityBehavior::class, $fieldDeclarations));
    }

    protected function parseShellServiceTask(ActivityImpl $activity, Element $serviceTaskElement, array $fieldDeclarations): void
    {
        $this->validateFieldDeclarationsForShell($serviceTaskElement, $fieldDeclarations);
        $activity->setActivityBehavior(ClassDelegateUtil::instantiateDelegate(ShellActivityBehavior::class, $fieldDeclarations));
    }

    protected function parseExternalServiceTask(
        ActivityImpl $activity,
        Element $serviceTaskElement,
        Element $propertiesElement
    ): void {
        $activity->setScope(true);
        $topicNameProvider = $this->parseTopic($serviceTaskElement, self::PROPERTYNAME_EXTERNAL_TASK_TOPIC);
        $priorityProvider = $this->parsePriority($serviceTaskElement, self::PROPERTYNAME_TASK_PRIORITY);
        $properties = BpmnParseUtil::parseExtensionProperties($propertiesElement);
        $activity->getProperties()->set(BpmnProperties::extensionProperties(), $properties);
        $errorEventDefinitions = $this->parseErrorEventDefinitions($activity, $serviceTaskElement);
        $activity->getProperties()->set(BpmnProperties::extensionErrorEventDefinition(), $errorEventDefinitions);
        $activity->setActivityBehavior(new ExternalTaskActivityBehavior($topicNameProvider, $priorityProvider));
    }

    protected function validateFieldDeclarationsForEmail(Element $serviceTaskElement, array $fieldDeclarations): void
    {
        $toDefined = false;
        $textOrHtmlDefined = false;
        foreach ($fieldDeclarations as $fieldDeclaration) {
            if ($fieldDeclaration->getName() == "to") {
                $toDefined = true;
            }
            if ($fieldDeclaration->getName() == "html") {
                $textOrHtmlDefined = true;
            }
            if ($fieldDeclaration->getName() == "text") {
                $textOrHtmlDefined = true;
            }
        }

        if (!$toDefined) {
            $this->addError("No recipient is defined on the mail activity", $serviceTaskElement);
        }
        if (!$textOrHtmlDefined) {
            $this->addError("Text or html field should be provided", $serviceTaskElement);
        }
    }

    protected function validateFieldDeclarationsForShell(Element $serviceTaskElement, array $fieldDeclarations): void
    {
        $shellCommandDefined = false;

        foreach ($fieldDeclarations as $fieldDeclaration) {
            $fieldName = $fieldDeclaration->getName();
            $fieldFixedValue = $fieldDeclaration->getValue();
            $fieldValue = $fieldFixedValue->getExpressionText();

            $shellCommandDefined |= $fieldName == "command";

            if (($fieldName == "wait" || $fieldName == "redirectError" || $fieldName == "cleanEnv") && strtolower($fieldValue) != "true" && strtolower($fieldValue) != "false") {
                $this->addError("undefined value for shell " . $fieldName . " parameter :" . $fieldValue, $serviceTaskElement);
            }
        }

        if (!$shellCommandDefined) {
            $this->addError("No shell command is defined on the shell activity", $serviceTaskElement);
        }
    }

    public function parseFieldDeclarations(Element $element): array
    {
        $fieldDeclarations = [];

        $elementWithFieldInjections = $element->element("extensionElements");
        if ($elementWithFieldInjections === null) { // Custom extensions will just
                                                    // have the <field.. as a
                                                    // subelement
            $elementWithFieldInjections = $element;
        }
        $fieldDeclarationElements = $elementWithFieldInjections->elementsNS(BpmnParser::BPMN_EXTENSIONS_NS, "field");
        if (!empty($fieldDeclarationElements)) {
            foreach ($fieldDeclarationElements as $fieldDeclarationElement) {
                $fieldDeclaration = $this->parseFieldDeclaration($element, $fieldDeclarationElement);
                if ($fieldDeclaration !== null) {
                    $fieldDeclarations[] = $fieldDeclaration;
                }
            }
        }

        return $fieldDeclarations;
    }

    protected function parseFieldDeclaration(Element $serviceTaskElement, Element $fieldDeclarationElement): ?FieldDeclaration
    {
        $fieldName = $fieldDeclarationElement->attribute("name");
        $fieldDeclaration = $this->parseStringFieldDeclaration($fieldDeclarationElement, $serviceTaskElement, $fieldName);
        if ($fieldDeclaration === null) {
            $fieldDeclaration = $this->parseExpressionFieldDeclaration($fieldDeclarationElement, $serviceTaskElement, $fieldName);
        }

        if ($fieldDeclaration === null) {
            $this->addError(
                "One of the following is mandatory on a field declaration: one of attributes stringValue|expression " .
                "or one of child elements string|expression",
                $serviceTaskElement
            );
        }
        return $fieldDeclaration;
    }

    protected function parseStringFieldDeclaration(Element $fieldDeclarationElement, Element $serviceTaskElement, ?string $fieldName): ?FieldDeclaration
    {
        try {
            $fieldValue = $this->getStringValueFromAttributeOrElement("stringValue", "string", $fieldDeclarationElement, $serviceTaskElement->attribute("id"));
            if ($fieldValue !== null) {
                return new FieldDeclaration($fieldName, Expression::class, new FixedValue($fieldValue));
            }
        } catch (ProcessEngineException $ae) {
            $this->addError("Error when paring field declarations: " . $ae->getMessage(), $serviceTaskElement);
        }
        return null;
    }

    protected function parseExpressionFieldDeclaration(Element $fieldDeclarationElement, Element $serviceTaskElement, ?string $fieldName): ?FieldDeclaration
    {
        try {
            $expression = $this->getStringValueFromAttributeOrElement(self::PROPERTYNAME_EXPRESSION, self::PROPERTYNAME_EXPRESSION, $fieldDeclarationElement, $serviceTaskElement->attribute("id"));
            if (!empty(trim($expression))) {
                return new FieldDeclaration($fieldName, ExpressionInterface::class, $this->expressionManager->createExpression($expression));
            }
        } catch (ProcessEngineException $ae) {
            $this->addError("Error when paring field declarations: " . $ae->getMessage(), $serviceTaskElement);
        }
        return null;
    }

    protected function getStringValueFromAttributeOrElement(?string $attributeName, ?string $elementName, Element $element, ?string $ancestorElementId): ?string
    {
        $value = null;

        $attributeValue = $element->attribute($attributeName);
        $childElement = $element->elementNS(BpmnParser::BPMN_EXTENSIONS_NS, $elementName);
        $stringElementText = null;

        if ($attributeValue !== null && $childElement !== null) {
            $this->addError("Can't use attribute '" . $attributeName . "' and element '" . $elementName . "' together, only use one", $element, $ancestorElementId);
        } elseif ($childElement !== null) {
            $stringElementText = $childElement->getText();
            if (empty($stringElementText)) {
                $this->addError("No valid value found in attribute '" . $attributeName . "' nor element '" . $elementName . "'", $element, $ancestorElementId);
            } else {
                // Use text of element
                $value = $stringElementText;
            }
        } elseif (!empty($attributeValue)) {
            // Using attribute
            $value = $attributeValue;
        }

        return $value;
    }

    /**
    * Parses a task with no specific type (behaves as passthrough).
    */
    public function parseTask(Element $taskElement, ScopeImpl $scope): ?ActivityImpl
    {
        $activity = $this->createActivityOnScope($taskElement, $scope);
        $activity->setActivityBehavior(new TaskActivityBehavior());

        $this->parseAsynchronousContinuationForActivity($taskElement, $activity);

        $this->parseExecutionListenersOnScope($taskElement, $activity);

        foreach ($this->parseListeners as $parseListener) {
            $parseListener->parseTask($taskElement, $scope, $activity);
        }
        return $activity;
    }

    /**
    * Parses a manual task.
    */
    public function parseManualTask(Element $manualTaskElement, ScopeImpl $scope): ?ActivityImpl
    {
        $activity = $this->createActivityOnScope($manualTaskElement, $scope);
        $activity->setActivityBehavior(new ManualTaskActivityBehavior());

        $this->parseAsynchronousContinuationForActivity($manualTaskElement, $activity);

        $this->parseExecutionListenersOnScope($manualTaskElement, $activity);

        foreach ($this->parseListeners as $parseListener) {
            $parseListener->parseManualTask($manualTaskElement, $scope, $activity);
        }
        return $activity;
    }

    /**
    * Parses a receive task.
    */
    public function parseReceiveTask(Element $receiveTaskElement, ScopeImpl $scope): ?ActivityImpl
    {
        $activity = $this->createActivityOnScope($receiveTaskElement, $scope);
        $activity->setActivityBehavior(new ReceiveTaskActivityBehavior());

        $this->parseAsynchronousContinuationForActivity($receiveTaskElement, $activity);

        $this->parseExecutionListenersOnScope($receiveTaskElement, $activity);

        if ($receiveTaskElement->attribute("messageRef") !== null) {
            $activity->setScope(true);
            $activity->setEventScope($activity);
            $declaration = $this->parseMessageEventDefinition($receiveTaskElement, $activity->getId());
            $declaration->setActivityId($activity->getActivityId());
            $declaration->setEventScopeActivityId($activity->getActivityId());
            $this->addEventSubscriptionDeclaration($declaration, $activity, $receiveTaskElement);
        }

        foreach ($this->parseListeners as $parseListener) {
            $parseListener->parseReceiveTask($receiveTaskElement, $scope, $activity);
        }
        return $activity;
    }

    /* userTask specific finals */

    protected const HUMAN_PERFORMER = "humanPerformer";
    protected const POTENTIAL_OWNER = "potentialOwner";

    protected const RESOURCE_ASSIGNMENT_EXPR = "resourceAssignmentExpression";
    protected const FORMAL_EXPRESSION = "formalExpression";

    protected const USER_PREFIX = "user(";
    protected const GROUP_PREFIX = "group(";

    protected const ASSIGNEE_EXTENSION = "assignee";
    protected const CANDIDATE_USERS_EXTENSION = "candidateUsers";
    protected const CANDIDATE_GROUPS_EXTENSION = "candidateGroups";
    protected const DUE_DATE_EXTENSION = "dueDate";
    protected const FOLLOW_UP_DATE_EXTENSION = "followUpDate";
    protected const PRIORITY_EXTENSION = "priority";

    /**
    * Parses a userTask declaration.
    */
    public function parseUserTask(Element $userTaskElement, ScopeImpl $scope): ?ActivityImpl
    {
        $activity = $this->createActivityOnScope($userTaskElement, $scope);

        $this->parseAsynchronousContinuationForActivity($userTaskElement, $activity);

        $taskDefinition = $this->parseTaskDefinition($userTaskElement, $activity->getId(), $activity, $scope->getProcessDefinition());
        $taskDecorator = new TaskDecorator($taskDefinition, $this->expressionManager);

        $userTaskActivity = new UserTaskActivityBehavior($taskDecorator);
        $activity->setActivityBehavior($userTaskActivity);

        $this->parseProperties($userTaskElement, $activity);
        $this->parseExecutionListenersOnScope($userTaskElement, $activity);

        foreach ($this->parseListeners as $parseListener) {
            $parseListener->parseUserTask($userTaskElement, $scope, $activity);
        }
        $properties = BpmnParseUtil::parseExtensionProperties($userTaskElement);
        return $activity;
    }

    public function parseTaskDefinition(Element $taskElement, ?string $taskDefinitionKey, ActivityImpl $activity, ProcessDefinitionEntity $processDefinition): ?TaskDefinition
    {
        $taskFormHandler = null;
        $taskFormHandlerClassName = $taskElement->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, "formHandlerClass");
        if ($taskFormHandlerClassName !== null) {
            $taskFormHandler = ReflectUtil::instantiate($taskFormHandlerClassName);
        } else {
            $taskFormHandler = new DefaultTaskFormHandler();
        }
        $taskFormHandler->parseConfiguration($taskElement, $this->deployment, $processDefinition, $this);

        $taskDefinition = new TaskDefinition(new DelegateTaskFormHandler($taskFormHandler, $this->deployment));

        $taskDefinition->setKey($taskDefinitionKey);
        $processDefinition->addTaskDefinition($taskDefinitionKey, $taskDefinition);

        $formDefinition = $this->parseFormDefinition($taskElement);
        $taskDefinition->setFormDefinition($formDefinition);

        $name = $taskElement->attribute("name");
        if (!empty($name)) {
            $taskDefinition->setNameExpression($this->expressionManager->createExpression($name));
        }

        $descriptionStr = self::parseDocumentation($taskElement);
        if ($descriptionStr !== null) {
            $taskDefinition->setDescriptionExpression($this->expressionManager->createExpression($descriptionStr));
        }

        $this->parseHumanPerformer($taskElement, $taskDefinition);
        $this->parsePotentialOwner($taskElement, $taskDefinition);

        // Activiti custom extension
        $this->parseUserTaskCustomExtensions($taskElement, $activity, $taskDefinition);

        return $taskDefinition;
    }

    protected function parseFormDefinition(Element $flowNodeElement): ?FormDefinition
    {
        $formDefinition = new FormDefinition();

        $formKeyAttribute = $flowNodeElement->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, "formKey");
        $formRefAttribute = $flowNodeElement->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, "formRef");

        if ($formKeyAttribute !== null && $formRefAttribute !== null) {
            $this->addError("Invalid element definition: only one of the attributes formKey and formRef is allowed.", $flowNodeElement);
        }

        if ($formKeyAttribute !== null) {
            $formDefinition->setFormKey($this->expressionManager->createExpression($formKeyAttribute));
        }

        if ($formRefAttribute !== null) {
            $formDefinition->setFormDefinitionKey($this->expressionManager->createExpression($formRefAttribute));

            $formRefBindingAttribute = $flowNodeElement->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, "formRefBinding");

            if ($formRefBindingAttribute === null || !in_array($formRefBindingAttribute, DefaultTaskFormHandler::ALLOWED_FORM_REF_BINDINGS)) {
                $this->addError(
                    "Invalid element definition: value for formRefBinding attribute has to be one of "
                    . json_encode(DefaultTaskFormHandler::ALLOWED_FORM_REF_BINDINGS) . " but was " . $formRefBindingAttribute,
                    $flowNodeElement
                );
            }


            if ($formRefBindingAttribute !== null) {
                $formDefinition->setFormDefinitionBinding($formRefBindingAttribute);
            }

            if (DefaultTaskFormHandler::FORM_REF_BINDING_VERSION == $formRefBindingAttribute) {
                $formRefVersionAttribute = $flowNodeElement->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, "formRefVersion");

                $formDefinitionVersion = $this->expressionManager->createExpression($formRefVersionAttribute);

                if ($formRefVersionAttribute !== null) {
                    $formDefinition->setFormDefinitionVersion($formDefinitionVersion);
                }
            }
        }

        return $formDefinition;
    }

    protected function parseHumanPerformer(Element $taskElement, TaskDefinition $taskDefinition): void
    {
        $humanPerformerElements = $taskElement->elements(self::HUMAN_PERFORMER);

        if (count($humanPerformerElements) > 1) {
            $this->addError("Invalid task definition: multiple " . self::HUMAN_PERFORMER . " sub elements defined for " . $taskDefinition->getNameExpression(), $taskElement);
        } elseif (count($humanPerformerElements) == 1) {
            $humanPerformerElement = $humanPerformerElements[0];
            if ($humanPerformerElement !== null) {
                $this->parseHumanPerformerResourceAssignment($humanPerformerElement, $taskDefinition);
            }
        }
    }

    protected function parsePotentialOwner(Element $taskElement, TaskDefinition $taskDefinition): void
    {
        $potentialOwnerElements = $taskElement->elements(self::POTENTIAL_OWNER);
        foreach ($potentialOwnerElements as $potentialOwnerElement) {
            $this->parsePotentialOwnerResourceAssignment($potentialOwnerElement, $taskDefinition);
        }
    }

    protected function parseHumanPerformerResourceAssignment(Element $performerElement, TaskDefinition $taskDefinition): void
    {
        $raeElement = $performerElement->element(self::RESOURCE_ASSIGNMENT_EXPR);
        if ($raeElement !== null) {
            $feElement = $raeElement->element(self::FORMAL_EXPRESSION);
            if ($feElement !== null) {
                $taskDefinition->setAssigneeExpression($this->expressionManager->createExpression($feElement->getText()));
            }
        }
    }

    protected function parsePotentialOwnerResourceAssignment(Element $performerElement, TaskDefinition $taskDefinition): void
    {
        $raeElement = $performerElement->element(self::RESOURCE_ASSIGNMENT_EXPR);
        if ($raeElement !== null) {
            $feElement = $raeElement->element(self::FORMAL_EXPRESSION);
            if ($feElement !== null) {
                $assignmentExpressions = $this->parseCommaSeparatedList($feElement->getText());
                foreach ($assignmentExpressions as $assignmentExpression) {
                    $assignmentExpression = trim($assignmentExpression);
                    if (str_starts_with($assignmentExpression, self::USER_PREFIX)) {
                        $userAssignementId = $this->getAssignmentId($assignmentExpression, self::USER_PREFIX);
                        $taskDefinition->addCandidateUserIdExpression($this->expressionManager->createExpression($userAssignementId));
                    } elseif (str_starts_with($assignmentExpression, self::GROUP_PREFIX)) {
                        $groupAssignementId = $this->getAssignmentId($assignmentExpression, self::GROUP_PREFIX);
                        $taskDefinition->addCandidateGroupIdExpression($this->expressionManager->createExpression($groupAssignementId));
                    } else { // default: given string is a goupId, as-is.
                        $taskDefinition->addCandidateGroupIdExpression($this->expressionManager->createExpression($assignmentExpression));
                    }
                }
            }
        }
    }

    protected function getAssignmentId(?string $expression, ?string $prefix): ?string
    {
        return trim(substr($expression, strlen($prefix), strlen($expression) - 1));
    }

    protected function parseUserTaskCustomExtensions(Element $taskElement, ActivityImpl $activity, TaskDefinition $taskDefinition): void
    {
        // assignee
        $assignee = $taskElement->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, self::ASSIGNEE_EXTENSION);
        if (!empty($assignee)) {
            if ($taskDefinition->getAssigneeExpression() === null) {
                $taskDefinition->setAssigneeExpression($this->expressionManager->createExpression($assignee));
            } else {
                $this->addError("Invalid usage: duplicate assignee declaration for task " . $taskDefinition->getNameExpression(), $taskElement);
            }
        }

        // Candidate users
        $candidateUsersString = $taskElement->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, self::CANDIDATE_USERS_EXTENSION);
        if (!empty($candidateUsersString)) {
            $candidateUsers = $this->parseCommaSeparatedList($candidateUsersString);
            foreach ($candidateUsers as $candidateUser) {
                $taskDefinition->addCandidateUserIdExpression($this->expressionManager->createExpression(trim($candidateUser)));
            }
        }

        // Candidate groups
        $candidateGroupsString = $taskElement->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, self::CANDIDATE_GROUPS_EXTENSION);
        if (!empty($candidateGroupsString)) {
            $candidateGroups = $this->parseCommaSeparatedList($candidateGroupsString);
            foreach ($candidateGroups as $candidateGroup) {
                $taskDefinition->addCandidateGroupIdExpression($this->expressionManager->createExpression(trim($candidateGroup)));
            }
        }

        // Task listeners
        $this->parseTaskExtensions($taskElement, $activity, $taskDefinition);

        // Due date
        $dueDateExpression = $taskElement->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, self::DUE_DATE_EXTENSION);
        if ($dueDateExpression !== null) {
            $taskDefinition->setDueDateExpression($this->expressionManager->createExpression($dueDateExpression));
        }

        // follow up date
        $followUpDateExpression = $taskElement->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, self::FOLLOW_UP_DATE_EXTENSION);
        if ($followUpDateExpression !== null) {
            $taskDefinition->setFollowUpDateExpression($this->expressionManager->createExpression($followUpDateExpression));
        }

        // Priority
        $priorityExpression = $taskElement->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, self::PRIORITY_EXTENSION);
        if ($priorityExpression !== null) {
            $taskDefinition->setPriorityExpression($this->expressionManager->createExpression($priorityExpression));
        }
    }

    /**
    * Parses the given String as a list of comma separated entries, where an
    * entry can possibly be an expression that has comma's.
    *
    * If somebody is smart enough to write a regex for this, please let us know.
    *
    * @return array the entries of the comma separated list, trimmed.
    */
    protected function parseCommaSeparatedList(?string $s): array
    {
        $result = [];
        if (!empty($s)) {
            $c = $s[0];

            $strb = "";
            $insideExpression = false;
            $len = strlen($s);
            for ($i = 0; $i < $len; $i += 1) {
                if ($c == '{' || $c == '$') {
                    $insideExpression = true;
                } elseif ($c == '}') {
                    $insideExpression = false;
                } elseif ($c == ',' && !$insideExpression) {
                    $result[] = trim($strb);
                    $strb = "";
                }

                if ($c != ',' || $insideExpression) {
                    $strb .= $c;
                }

                if ($i + 1 < $len) {
                    $c = $s[$i + 1];
                }
            }

            if (strlen($strb) > 0) {
                $result[] = trim($strb);
            }
        }
        return $result;
    }

    protected function parseTaskExtensions(Element $userTaskElement, ActivityImpl $activity, TaskDefinition $taskDefinition): void
    {
        $extentionsElement = $userTaskElement->element("extensionElements");
        if ($extentionsElement !== null) {
            $taskListenerElements = $extentionsElement->elementsNS(BpmnParser::BPMN_EXTENSIONS_NS, "taskListener");
            foreach ($taskListenerElements as $taskListenerElement) {
                $eventName = $taskListenerElement->attribute("event");
                if (!empty($eventName)) {
                    if (
                        TaskListenerInterface::EVENTNAME_CREATE == $eventName || TaskListenerInterface::EVENTNAME_ASSIGNMENT == $eventName
                        || TaskListenerInterface::EVENTNAME_COMPLETE == $eventName || TaskListenerInterface::EVENTNAME_UPDATE == $eventName
                        || TaskListenerInterface::EVENTNAME_DELETE == $eventName
                    ) {
                        $taskListener = $this->parseTaskListener($taskListenerElement, $activity->getId());
                        $taskDefinition->addTaskListener($eventName, $taskListener);
                    } elseif (TaskListenerInterface::EVENTNAME_TIMEOUT == $eventName) {
                        $taskListener = $this->parseTimeoutTaskListener($taskListenerElement, $activity, $taskDefinition);
                        $taskDefinition->addTimeoutTaskListener($taskListenerElement->attribute("id"), $taskListener);
                    } else {
                        $this->addError("Attribute 'event' must be one of {create|assignment|complete|update|delete|timeout}", $userTaskElement);
                    }
                } else {
                    $this->addError("Attribute 'event' is mandatory on taskListener", $userTaskElement);
                }
            }

            $properties = BpmnParseUtil::parseExtensionProperties($userTaskElement);
            $activity->getProperties()->set(BpmnProperties::extensionProperties(), $properties);
        }
    }

    protected function parseTaskListener(Element $taskListenerElement, ?string $taskElementId): ?TaskListenerInterface
    {
        $taskListener = null;

        $className = str_replace('.', '\\', $taskListenerElement->attribute(self::PROPERTYNAME_CLASS));
        $expression = $taskListenerElement->attribute(self::PROPERTYNAME_EXPRESSION);
        $delegateExpression = $taskListenerElement->attribute(self::PROPERTYNAME_DELEGATE_EXPRESSION);
        $scriptElement = $taskListenerElement->elementNS(BpmnParser::BPMN_EXTENSIONS_NS, "script");

        if ($className !== null) {
            $taskListener = new ClassDelegateTaskListener($className, $this->parseFieldDeclarations($taskListenerElement));
        } elseif ($expression !== null) {
            $taskListener = new ExpressionTaskListener($this->expressionManager->createExpression($expression));
        } elseif ($delegateExpression !== null) {
            $taskListener = new DelegateExpressionTaskListener($this->expressionManager->createExpression($delegateExpression), $this->parseFieldDeclarations($taskListenerElement));
        } elseif ($scriptElement !== null) {
            try {
                $executableScript = BpmnParseUtil::parseScript($scriptElement);
                if ($executableScript !== null) {
                    $taskListener = new ScriptTaskListener($executableScript);
                }
            } catch (BpmnParseException $e) {
                $this->addError($e, $taskElementId);
            }
        } else {
            $this->addError("Element 'class', 'expression', 'delegateExpression' or 'script' is mandatory on taskListener", $taskListenerElement, $taskElementId);
        }
        return $taskListener;
    }

    protected function parseTimeoutTaskListener(Element $taskListenerElement, ActivityImpl $timerActivity, TaskDefinition $taskDefinition): ?TaskListenerInterface
    {
        $listenerId = $taskListenerElement->attribute("id");
        $timerActivityId = $timerActivity->getId();
        if ($listenerId === null) {
            $this->addError("Element 'id' is mandatory on taskListener of type 'timeout'", $taskListenerElement, $timerActivityId);
        }
        $timerEventDefinition = $taskListenerElement->element(self::TIMER_EVENT_DEFINITION);
        if ($timerEventDefinition === null) {
            $this->addError("Element 'timerEventDefinition' is mandatory on taskListener of type 'timeout'", $taskListenerElement, $timerActivityId);
        }
        $timerActivity->setScope(true);
        $timerActivity->setEventScope($timerActivity);
        $timerDeclaration = $this->parseTimer($timerEventDefinition, $timerActivity, TimerTaskListenerJobHandler::TYPE);
        $timerDeclaration->setRawJobHandlerConfiguration(
            $timerActivityId . TimerEventJobHandler::JOB_HANDLER_CONFIG_PROPERTY_DELIMITER .
            TimerEventJobHandler::JOB_HANDLER_CONFIG_TASK_LISTENER_PREFIX . $listenerId
        );
        $this->addTimerListenerDeclaration($listenerId, $timerActivity, $timerDeclaration);

        return $this->parseTaskListener($taskListenerElement, $timerActivityId);
    }

    /**
    * Parses the end events of a certain level in the process (process,
    * subprocess or another scope).
    *
    * @param parentElement
    *          The 'parent' element that contains the end events (process,
    *          subprocess).
    * @param scope
    *          The ScopeImpl to which the end events must be added.
    */
    public function parseEndEvents(Element $parentElement, ScopeImpl $scope): void
    {
        foreach ($parentElement->elements("endEvent") as $endEventElement) {
            $activity = $this->createActivityOnScope($endEventElement, $scope);

            $errorEventDefinition = $endEventElement->element(self::ERROR_EVENT_DEFINITION);
            $cancelEventDefinition = $endEventElement->element(self::CANCEL_EVENT_DEFINITION);
            $terminateEventDefinition = $endEventElement->element("terminateEventDefinition");
            $messageEventDefinitionElement = $endEventElement->element(self::MESSAGE_EVENT_DEFINITION);
            $signalEventDefinition = $endEventElement->element(self::SIGNAL_EVENT_DEFINITION);
            $compensateEventDefinitionElement = $endEventElement->element(self::COMPENSATE_EVENT_DEFINITION);
            $escalationEventDefinition = $endEventElement->element(self::ESCALATION_EVENT_DEFINITION);

            $isServiceTaskLike = $this->isServiceTaskLike($messageEventDefinitionElement);

            $activityId = $activity->getId();
            if ($errorEventDefinition !== null) { // error end event
                $errorRef = $errorEventDefinition->attribute("errorRef");

                if (empty($errorRef)) {
                    $this->addError("'errorRef' attribute is mandatory on error end event", $errorEventDefinition, $activityId);
                } else {
                    $error = null;
                    if (array_key_exists($errorRef, $this->errorsMap)) {
                        $error = $this->errorsMap[$errorRef];
                    }
                    if ($error !== null && (empty($error->getErrorCode()))) {
                        $this->addError(
                            "'errorCode' is mandatory on errors referenced by throwing error event definitions, but the error '" . $error->getId() . "' does not define one.",
                            $errorEventDefinition,
                            $activityId
                        );
                    }
                    $activity->getProperties()->set(BpmnProperties::type(), ActivityTypes::END_EVENT_ERROR);
                    if ($error !== null) {
                        $activity->setActivityBehavior(new ErrorEndEventActivityBehavior($error->getErrorCode(), $error->getErrorMessageExpression()));
                    } else {
                        $activity->setActivityBehavior(new ErrorEndEventActivityBehavior($errorRef, null));
                    }
                }
            } elseif ($cancelEventDefinition !== null) {
                if ($scope->getProperty(BpmnProperties::type()->getName()) === null || $scope->getProperty(BpmnProperties::type()->getName()) != "transaction") {
                    $this->addError("end event with cancelEventDefinition only supported inside transaction subprocess", $cancelEventDefinition, $activityId);
                } else {
                    $activity->getProperties()->set(BpmnProperties::type(), ActivityTypes::END_EVENT_CANCEL);
                    $activity->setActivityBehavior(new CancelEndEventActivityBehavior());
                    $activity->setActivityStartBehavior(ActivityStartBehavior::INTERRUPT_FLOW_SCOPE);
                    $activity->setProperty(self::PROPERTYNAME_THROWS_COMPENSATION, true);
                    $activity->setScope(true);
                }
            } elseif ($terminateEventDefinition !== null) {
                $activity->getProperties()->set(BpmnProperties::type(), ActivityTypes::END_EVENT_TERMINATE);
                $activity->setActivityBehavior(new TerminateEndEventActivityBehavior());
                $activity->setActivityStartBehavior(ActivityStartBehavior::INTERRUPT_FLOW_SCOPE);
            } elseif ($messageEventDefinitionElement !== null) {
                if ($isServiceTaskLike) {
                    // CAM-436 same behaviour as service task
                    $this->parseServiceTaskLike(
                        $activity,
                        ActivityTypes::END_EVENT_MESSAGE,
                        $messageEventDefinitionElement,
                        $endEventElement,
                        $scope
                    );
                    $activity->getProperties()->set(BpmnProperties::type(), ActivityTypes::END_EVENT_MESSAGE);
                } else {
                    // default to non behavior if no service task
                    // properties have been specified
                    $activity->setActivityBehavior(new IntermediateThrowNoneEventActivityBehavior());
                }
            } elseif ($signalEventDefinition !== null) {
                $activity->getProperties()->set(BpmnProperties::type(), ActivityTypes::END_EVENT_SIGNAL);
                $signalDefinition = $this->parseSignalEventDefinition($signalEventDefinition, true, $activityId);
                $activity->setActivityBehavior(new ThrowSignalEventActivityBehavior($signalDefinition));
            } elseif ($compensateEventDefinitionElement !== null) {
                $activity->getProperties()->set(BpmnProperties::type(), ActivityTypes::END_EVENT_COMPENSATION);
                $compensateEventDefinition = $this->parseThrowCompensateEventDefinition($compensateEventDefinitionElement, $scope, $endEventElement->attribute("id"));
                $activity->setActivityBehavior(new CompensationEventActivityBehavior($compensateEventDefinition));
                $activity->setProperty(self::PROPERTYNAME_THROWS_COMPENSATION, true);
                $activity->setScope(true);
            } elseif ($escalationEventDefinition !== null) {
                $activity->getProperties()->set(BpmnProperties::type(), ActivityTypes::END_EVENT_ESCALATION);

                $escalation = $this->findEscalationForEscalationEventDefinition($escalationEventDefinition, $activityId);
                if ($escalation !== null && $escalation->getEscalationCode() === null) {
                    $this->addError("escalation end event must have an 'escalationCode'", $escalationEventDefinition, $activityId);
                }
                $activity->setActivityBehavior(new ThrowEscalationEventActivityBehavior($escalation));
            } else { // default: none end event
                $activity->getProperties()->set(BpmnProperties::type(), ActivityTypes::END_EVENT_NONE);
                $activity->setActivityBehavior(new NoneEndEventActivityBehavior());
            }

            if ($activity !== null) {
                $this->parseActivityInputOutput($endEventElement, $activity);
            }

            $this->parseAsynchronousContinuationForActivity($endEventElement, $activity);

            $this->parseExecutionListenersOnScope($endEventElement, $activity);

            foreach ($this->parseListeners as $parseListener) {
                $parseListener->parseEndEvent($endEventElement, $scope, $activity);
            }

            if ($isServiceTaskLike) {
                // activity behavior could be set by a listener (e.g. connector); thus,
                // check is after listener invocation
                $this->validateServiceTaskLike(
                    $activity,
                    ActivityTypes::END_EVENT_MESSAGE,
                    $messageEventDefinitionElement
                );
            }
        }
    }

    /**
    * Parses the boundary events of a certain 'level' (process, subprocess or
    * other scope).
    *
    * Note that the boundary events are not parsed during the parsing of the bpmn
    * activities, since the semantics are different (boundaryEvent needs to be
    * added as nested activity to the reference activity on PVM level).
    *
    * @param parentElement
    *          The 'parent' element that contains the activities (process,
    *          subprocess).
    * @param flowScope
    *          The ScopeImpl to which the activities must be added.
    */
    public function parseBoundaryEvents(Element $parentElement, ScopeImpl $flowScope): void
    {
        foreach ($parentElement->elements("boundaryEvent") as $boundaryEventElement) {
            // The boundary event is attached to an activity, reference by the
            // 'attachedToRef' attribute
            $attachedToRef = $boundaryEventElement->attribute("attachedToRef");
            if (empty($attachedToRef)) {
                $this->addError("AttachedToRef is required when using a timerEventDefinition", $boundaryEventElement);
            }

            // Representation structure-wise is a nested activity in the activity to
            // which its attached
            $id = $boundaryEventElement->attribute("id");

            //LOG.parsingElement("boundary event", id);

            // Depending on the sub-element definition, the correct activityBehavior
            // parsing is selected
            $timerEventDefinition = $boundaryEventElement->element(self::TIMER_EVENT_DEFINITION);
            $errorEventDefinition = $boundaryEventElement->element(self::ERROR_EVENT_DEFINITION);
            $signalEventDefinition = $boundaryEventElement->element(self::SIGNAL_EVENT_DEFINITION);
            $cancelEventDefinition = $boundaryEventElement->element(self::CANCEL_EVENT_DEFINITION);
            $compensateEventDefinition = $boundaryEventElement->element(self::COMPENSATE_EVENT_DEFINITION);
            $messageEventDefinition = $boundaryEventElement->element(self::MESSAGE_EVENT_DEFINITION);
            $escalationEventDefinition = $boundaryEventElement->element(self::ESCALATION_EVENT_DEFINITION);
            $conditionalEventDefinition = $boundaryEventElement->element(self::CONDITIONAL_EVENT_DEFINITION);

            // create the boundary event activity
            $boundaryEventActivity = $this->createActivityOnScope($boundaryEventElement, $flowScope);
            $this->parseAsynchronousContinuation($boundaryEventElement, $boundaryEventActivity);

            $attachedActivity = $flowScope->findActivityAtLevelOfSubprocess($attachedToRef);
            if ($attachedActivity === null) {
                $this->addError(
                    "Invalid reference in boundary event. Make sure that the referenced activity is defined in the same scope as the boundary event",
                    $boundaryEventElement
                );
            }

            // determine the correct event scope (the scope in which the boundary event catches events)
            if ($compensateEventDefinition === null) {
                $multiInstanceScope = $this->getMultiInstanceScope($attachedActivity);
                if ($multiInstanceScope !== null) {
                    // if the boundary event is attached to a multi instance activity,
                    // then the scope of the boundary event is the multi instance body.
                    $boundaryEventActivity->setEventScope($multiInstanceScope);
                } else {
                    $attachedActivity->setScope(true);
                    $boundaryEventActivity->setEventScope($attachedActivity);
                }
            } else {
                $boundaryEventActivity->setEventScope($attachedActivity);
            }

            // except escalation, by default is assumed to abort the activity
            $cancelActivityAttr = $boundaryEventElement->attribute("cancelActivity", "true");
            $isCancelActivity = $cancelActivityAttr === "true";

            // determine start behavior
            if ($isCancelActivity) {
                $boundaryEventActivity->setActivityStartBehavior(ActivityStartBehavior::CANCEL_EVENT_SCOPE);
            } else {
                $boundaryEventActivity->setActivityStartBehavior(ActivityStartBehavior::CONCURRENT_IN_FLOW_SCOPE);
            }

            // Catch event behavior is the same for most types
            $behavior = new BoundaryEventActivityBehavior();
            if ($timerEventDefinition !== null) {
                $this->parseBoundaryTimerEventDefinition($timerEventDefinition, $isCancelActivity, $boundaryEventActivity);
            } elseif ($errorEventDefinition !== null) {
                $this->parseBoundaryErrorEventDefinition($errorEventDefinition, $boundaryEventActivity);
            } elseif ($signalEventDefinition !== null) {
                $this->parseBoundarySignalEventDefinition($signalEventDefinition, $isCancelActivity, $boundaryEventActivity);
            } elseif ($cancelEventDefinition !== null) {
                $behavior = $this->parseBoundaryCancelEventDefinition($cancelEventDefinition, $boundaryEventActivity);
            } elseif ($compensateEventDefinition !== null) {
                $this->parseBoundaryCompensateEventDefinition($compensateEventDefinition, $boundaryEventActivity);
            } elseif ($messageEventDefinition !== null) {
                $this->parseBoundaryMessageEventDefinition($messageEventDefinition, $isCancelActivity, $boundaryEventActivity);
            } elseif ($escalationEventDefinition !== null) {
                if (
                    $attachedActivity->isSubProcessScope() || $attachedActivity->getActivityBehavior() instanceof CallActivityBehavior ||
                    $attachedActivity->getActivityBehavior() instanceof UserTaskActivityBehavior
                ) {
                    $this->parseBoundaryEscalationEventDefinition($escalationEventDefinition, $isCancelActivity, $boundaryEventActivity);
                } else {
                    $this->addError("An escalation boundary event should only be attached to a subprocess, a call activity or an user task", $boundaryEventElement);
                }
            } elseif ($conditionalEventDefinition !== null) {
                $behavior = $this->parseBoundaryConditionalEventDefinition($conditionalEventDefinition, $isCancelActivity, $boundaryEventActivity);
            } else {
                $this->addError("Unsupported boundary event type", $boundaryEventElement);
            }

            $this->ensureNoIoMappingDefined($boundaryEventElement);

            $boundaryEventActivity->setActivityBehavior($behavior);

            $this->parseExecutionListenersOnScope($boundaryEventElement, $boundaryEventActivity);

            foreach ($this->parseListeners as $parseListener) {
                $parseListener->parseBoundaryEvent($boundaryEventElement, $flowScope, $boundaryEventActivity);
            }
        }
    }

    public function parseErrorEventDefinitions(ActivityImpl $activity, Element $scopeElement): array
    {
        $errorEventDefinitions = [];
        $extensionElements = $scopeElement->element("extensionElements");
        if (!empty($extensionElements)) {
            $errorEventDefinitionElements = $extensionElements->elements("errorEventDefinition");
            foreach ($errorEventDefinitionElements as $errorEventDefinitionElement) {
                $errorRef = $errorEventDefinitionElement->attribute("errorRef");
                $error = null;
                if ($errorRef !== null) {
                    $expression = $errorEventDefinitionElement->attribute("expression");
                    $error = array_key_exists($errorRef, $this->errorsMap) ? $this->errorsMap[$errorRef] : null;
                    $definition = new ErrorEventDefinition($activity->getId(), $this->expressionManager->createExpression($expression));
                    $definition->setErrorCode($error === null ? $errorRef : $error->getErrorCode());
                    $this->setErrorCodeVariableOnErrorEventDefinition($errorEventDefinitionElement, $definition);
                    $this->setErrorMessageVariableOnErrorEventDefinition($errorEventDefinitionElement, $definition);

                    $errorEventDefinitions[] = $definition;
                }
            }
        }
        return $errorEventDefinitions;
    }

    protected function getMultiInstanceScope(ActivityImpl $activity): ?ActivityImpl
    {
        if ($activity->isMultiInstance()) {
            return $activity->getParentFlowScopeActivity();
        } else {
            return null;
        }
    }

    /**
    * Parses a boundary timer event. The end-result will be that the given nested
    * activity will get the appropriate ActivityBehavior.
    *
    * @param timerEventDefinition
    *          The XML element corresponding with the timer event details
    * @param interrupting
    *          Indicates whether this timer is interrupting.
    * @param boundaryActivity
    *          The activity which maps to the structure of the timer event on the
    *          boundary of another activity. Note that this is NOT the activity
    *          onto which the boundary event is attached, but a nested activity
    *          inside this activity, specifically created for this event.
    */
    public function parseBoundaryTimerEventDefinition(Element $timerEventDefinition, bool $interrupting, ActivityImpl $boundaryActivity): void
    {
        $boundaryActivity->getProperties()->set(BpmnProperties::type(), ActivityTypes::BOUNDARY_TIMER);
        $timerDeclaration = $this->parseTimer($timerEventDefinition, $boundaryActivity, TimerExecuteNestedActivityJobHandler::TYPE);

        // ACT-1427
        if ($interrupting) {
            $timerDeclaration->setInterruptingTimer(true);

            $timeCycleElement = $timerEventDefinition->element("timeCycle");
            if ($timeCycleElement !== null) {
                $this->addTimeCycleWarning($timeCycleElement, "cancelling boundary", $boundaryActivity->getId());
            }
        }

        $this->addTimerDeclaration($boundaryActivity->getEventScope(), $timerDeclaration);

        foreach ($this->parseListeners as $parseListener) {
            $parseListener->parseBoundaryTimerEventDefinition($timerEventDefinition, $interrupting, $boundaryActivity);
        }
    }

    public function parseBoundarySignalEventDefinition(Element $element, bool $interrupting, ActivityImpl $signalActivity): void
    {
        $signalActivity->getProperties()->set(BpmnProperties::type(), ActivityTypes::BOUNDARY_SIGNAL);

        $signalDefinition = $this->parseSignalEventDefinition($element, false, $signalActivity->getId());
        if ($signalActivity->getId() === null) {
            $this->addError("boundary event has no id", $element);
        }
        $signalDefinition->setActivityId($signalActivity->getId());
        $this->addEventSubscriptionDeclaration($signalDefinition, $signalActivity->getEventScope(), $element);

        foreach ($this->parseListeners as $parseListener) {
            $parseListener->parseBoundarySignalEventDefinition($element, $interrupting, $signalActivity);
        }
    }

    public function parseBoundaryMessageEventDefinition(Element $element, bool $interrupting, ActivityImpl $messageActivity): void
    {
        $messageActivity->getProperties()->set(BpmnProperties::type(), ActivityTypes::BOUNDARY_MESSAGE);

        $messageEventDefinition = $this->parseMessageEventDefinition($element, $messageActivity->getId());
        if ($messageActivity->getId() === null) {
            $this->addError("boundary event has no id", $element);
        }
        $messageEventDefinition->setActivityId($messageActivity->getId());
        $this->addEventSubscriptionDeclaration($messageEventDefinition, $messageActivity->getEventScope(), $element);

        foreach ($this->parseListeners as $parseListener) {
            $parseListener->parseBoundaryMessageEventDefinition($element, $interrupting, $messageActivity);
        }
    }

    protected function parseTimerStartEventDefinition(Element $timerEventDefinition, ActivityImpl $timerActivity, ProcessDefinitionEntity $processDefinition): void
    {
        $timerActivity->getProperties()->set(BpmnProperties::type(), ActivityTypes::START_EVENT_TIMER);
        $timerDeclaration = $this->parseTimer($timerEventDefinition, $timerActivity, TimerStartEventJobHandler::TYPE);
        $timerDeclaration->setRawJobHandlerConfiguration($processDefinition->getKey());

        $timerDeclarations = $processDefinition->getProperty(self::PROPERTYNAME_START_TIMER);
        if ($timerDeclarations === null) {
            $timerDeclarations = [];
            $processDefinition->setProperty(self::PROPERTYNAME_START_TIMER, $timerDeclarations);
        }
        $processDefinition->addProperty(self::PROPERTYNAME_START_TIMER, $timerDeclaration);
    }

    protected function parseTimerStartEventDefinitionForEventSubprocess(Element $timerEventDefinition, ActivityImpl $timerActivity, bool $interrupting): void
    {
        $timerActivity->getProperties()->set(BpmnProperties::type(), ActivityTypes::START_EVENT_TIMER);

        $timerDeclaration = $this->parseTimer($timerEventDefinition, $timerActivity, TimerStartEventSubprocessJobHandler::TYPE);

        $timerDeclaration->setActivity($timerActivity);
        $timerDeclaration->setEventScopeActivityId($timerActivity->getEventScope()->getId());
        $timerDeclaration->setRawJobHandlerConfiguration($timerActivity->getFlowScope()->getId());
        $timerDeclaration->setInterruptingTimer($interrupting);

        if ($interrupting) {
            $timeCycleElement = $timerEventDefinition->element("timeCycle");
            if ($timeCycleElement !== null) {
                $this->addTimeCycleWarning($timeCycleElement, "interrupting start", $timerActivity->getId());
            }
        }

        $this->addTimerDeclaration($timerActivity->getEventScope(), $timerDeclaration);
    }

    protected function parseEventDefinitionForSubprocess(EventSubscriptionDeclaration $subscriptionDeclaration, ActivityImpl $activity, Element $element): void
    {
        $subscriptionDeclaration->setActivityId($activity->getId());
        $subscriptionDeclaration->setEventScopeActivityId($activity->getEventScope()->getId());
        $subscriptionDeclaration->setStartEvent(false);
        $this->addEventSubscriptionDeclaration($subscriptionDeclaration, $activity->getEventScope(), $element);
    }

    protected function parseIntermediateSignalEventDefinition(Element $element, ActivityImpl $signalActivity): void
    {
        $signalActivity->getProperties()->set(BpmnProperties::type(), ActivityTypes::INTERMEDIATE_EVENT_SIGNAL);

        $this->parseSignalCatchEventDefinition($element, $signalActivity, false);

        foreach ($this->parseListeners as $parseListener) {
            $parseListener->parseIntermediateSignalCatchEventDefinition($element, $signalActivity);
        }
    }

    protected function parseSignalCatchEventDefinition(Element $element, ActivityImpl $signalActivity, bool $isStartEvent): void
    {
        $signalDefinition = $this->parseSignalEventDefinition($element, false, $signalActivity->getId());
        $signalDefinition->setActivityId($signalActivity->getId());
        $signalDefinition->setStartEvent($isStartEvent);
        $this->addEventSubscriptionDeclaration($signalDefinition, $signalActivity->getEventScope(), $element);

        $catchingAsyncDeclaration = new EventSubscriptionJobDeclaration($signalDefinition);
        $catchingAsyncDeclaration->setJobPriorityProvider($signalActivity->getProperty(self::PROPERTYNAME_JOB_PRIORITY));
        $catchingAsyncDeclaration->setActivity($signalActivity);
        $signalDefinition->setJobDeclaration($catchingAsyncDeclaration);
        $this->addEventSubscriptionJobDeclaration($catchingAsyncDeclaration, $signalActivity, $element);
    }

    /**
    * Parses the Signal Event Definition XML including payload definition.
    *
    * @param signalEventDefinitionElement the Signal Event Definition element
    * @param isThrowing true if a Throwing signal event is being parsed
    * @return
    */
    protected function parseSignalEventDefinition(Element $signalEventDefinitionElement, bool $isThrowing, ?string $signalElementId): ?EventSubscriptionDeclaration
    {
        $signalRef = $signalEventDefinitionElement->attribute("signalRef");
        if ($signalRef === null) {
            $this->addError("signalEventDefinition does not have required property 'signalRef'", $signalEventDefinitionElement, $signalElementId);
            return null;
        } else {
            $resolvedRef = $this->resolveName($signalRef);
            if (!array_key_exists($resolvedRef, $this->signals)) {
                $this->addError("Could not find signal with id '" . $signalRef . "'", $signalEventDefinitionElement, $signalElementId);
            } else {
                $signalDefinition = $this->signals[$resolvedRef];
            }

            $signalEventDefinition = null;
            if ($isThrowing) {
                $payload = new CallableElement();
                $this->parseInputParameter($signalEventDefinitionElement, $payload);
                $signalEventDefinition = new EventSubscriptionDeclaration($signalDefinition->getExpression(), EventType::signal(), $payload);
            } else {
                $signalEventDefinition = new EventSubscriptionDeclaration($signalDefinition->getExpression(), EventType::signal());
            }

            $throwingAsync = strtolower($signalEventDefinitionElement->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, "async", "false")) == true;
            $signalEventDefinition->setAsync($throwingAsync);

            return $signalEventDefinition;
        }
    }

    protected function parseIntermediateTimerEventDefinition(Element $timerEventDefinition, ActivityImpl $timerActivity): void
    {
        $timerActivity->getProperties()->set(BpmnProperties::type(), ActivityTypes::INTERMEDIATE_EVENT_TIMER);
        $timerDeclaration = $this->parseTimer($timerEventDefinition, $timerActivity, TimerCatchIntermediateEventJobHandler::TYPE);

        $timeCycleElement = $timerEventDefinition->element("timeCycle");
        if ($timeCycleElement !== null) {
            $this->addTimeCycleWarning($timeCycleElement, "intermediate catch", $timerActivity->getId());
        }

        $this->addTimerDeclaration($timerActivity->getEventScope(), $timerDeclaration);

        foreach ($this->parseListeners as $parseListener) {
            $parseListener->parseIntermediateTimerEventDefinition($timerEventDefinition, $timerActivity);
        }
    }

    protected function parseTimer(Element $timerEventDefinition, ActivityImpl $timerActivity, ?string $jobHandlerType): ?TimerDeclarationImpl
    {
        // TimeDate
        $type = TimerDeclarationType::DATE;
        $expression = $this->parseExpression($timerEventDefinition, "timeDate");
        // TimeCycle
        if ($expression === null) {
            $type = TimerDeclarationType::CYCLE;
            $expression = $this->parseExpression($timerEventDefinition, "timeCycle");
        }
        // TimeDuration
        if ($expression === null) {
            $type = TimerDeclarationType::DURATION;
            $expression = $this->parseExpression($timerEventDefinition, "timeDuration");
        }
        // neither date, cycle or duration configured!
        if ($expression === null) {
            $this->addError("Timer needs configuration (either timeDate, timeCycle or timeDuration is needed).", $timerEventDefinition, $timerActivity->getId());
        }

        // Parse the timer declaration
        // TODO move the timer declaration into the bpmn activity or next to the TimerSession
        $timerDeclaration = new TimerDeclarationImpl($expression, $type, $jobHandlerType);
        $timerDeclaration->setRawJobHandlerConfiguration($timerActivity->getId());
        $timerDeclaration->setExclusive(strtolower($timerEventDefinition->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, "exclusive", JobEntity::DEFAULT_EXCLUSIVE)) == "true");
        if ($timerActivity->getId() === null) {
            $this->addError("Attribute \"id\" is required!", $timerEventDefinition);
        }
        $timerDeclaration->setActivity($timerActivity);
        $timerDeclaration->setJobConfiguration($type . ": " . $expression->getExpressionText());
        $this->addJobDeclarationToProcessDefinition($timerDeclaration, $timerActivity->getProcessDefinition());

        $timerDeclaration->setJobPriorityProvider($timerActivity->getProperty(self::PROPERTYNAME_JOB_PRIORITY));

        return $timerDeclaration;
    }

    protected function parseExpression(Element $parent, ?string $name): ?ExpressionInterface
    {
        $value = $parent->element($name);
        if (!empty($value)) {
            $expressionText = trim($value->getText());
            return $this->expressionManager->createExpression($expressionText);
        }
        return null;
    }

    public function parseBoundaryErrorEventDefinition(Element $errorEventDefinition, ActivityImpl $boundaryEventActivity): void
    {
        $boundaryEventActivity->getProperties()->set(BpmnProperties::type(), ActivityTypes::BOUNDARY_ERROR);

        $errorRef = $errorEventDefinition->attribute("errorRef");
        $error = null;
        $definition = new ErrorEventDefinition($boundaryEventActivity->getId());
        if ($errorRef !== null) {
            if (array_key_exists($errorRef, $this->errorsMap)) {
                $error = $this->errorsMap[$errorRef];
            }
            $definition->setErrorCode($error === null ? $errorRef : $error->getErrorCode());
        }
        $this->setErrorCodeVariableOnErrorEventDefinition($errorEventDefinition, $definition);
        $this->setErrorMessageVariableOnErrorEventDefinition($errorEventDefinition, $definition);

        $this->addErrorEventDefinition($definition, $boundaryEventActivity->getEventScope());

        foreach ($this->parseListeners as $parseListener) {
            $parseListener->parseBoundaryErrorEventDefinition($errorEventDefinition, true, $boundaryEventActivity->getEventScope(), $boundaryEventActivity);
        }
    }

    protected function addErrorEventDefinition(ErrorEventDefinition $errorEventDefinition, ScopeImpl $catchingScope): void
    {
        $catchingScope->getProperties()->addListItem(BpmnProperties::errorEventDefinitions(), $errorEventDefinition);

        $catchingScope->sortProperties(BpmnProperties::errorEventDefinitions(), function ($prop1, $prop2) {
            return ($prop1->getPrecedence() >= $prop2->getPrecedence()) ? 1 : -1;
        });
    }

    protected function parseBoundaryEscalationEventDefinition(Element $escalationEventDefinitionElement, bool $cancelActivity, ActivityImpl $boundaryEventActivity): void
    {
        $boundaryEventActivity->getProperties()->set(BpmnProperties::type(), ActivityTypes::BOUNDARY_ESCALATION);

        $escalationEventDefinition = $this->createEscalationEventDefinitionForEscalationHandler($escalationEventDefinitionElement, $boundaryEventActivity, $cancelActivity, $boundaryEventActivity->getId());
        $this->addEscalationEventDefinition($boundaryEventActivity->getEventScope(), $escalationEventDefinition, $escalationEventDefinitionElement, $boundaryEventActivity->getId());

        foreach ($this->parseListeners as $parseListener) {
            $parseListener->parseBoundaryEscalationEventDefinition($escalationEventDefinitionElement, $cancelActivity, $boundaryEventActivity);
        }
    }

    /**
    * Find the referenced escalation of the given escalation event definition.
    * Add errors if the referenced escalation not found.
    *
    * @return referenced escalation or <code>null</code>, if referenced escalation not found
    */
    protected function findEscalationForEscalationEventDefinition(Element $escalationEventDefinition, ?string $escalationElementId): ?Escalation
    {
        $escalationRef = $escalationEventDefinition->attribute("escalationRef");
        if ($escalationRef === null) {
            $this->addError("escalationEventDefinition does not have required attribute 'escalationRef'", $escalationEventDefinition, $escalationElementId);
        } elseif (!array_key_exists($escalationRef, $this->escalations)) {
            $this->addError("could not find escalation with id '" . $escalationRef . "'", $escalationEventDefinition, $escalationElementId);
        } else {
            return $this->escalations[$escalationRef];
        }
        return null;
    }

    protected function createEscalationEventDefinitionForEscalationHandler(Element $escalationEventDefinitionElement, ActivityImpl $escalationHandler, bool $cancelActivity, ?string $parentElementId): EscalationEventDefinition
    {
        $escalationEventDefinition = new EscalationEventDefinition($escalationHandler, $cancelActivity);

        $escalationRef = $escalationEventDefinitionElement->attribute("escalationRef");
        if ($escalationRef !== null) {
            if (!array_key_exists($escalationRef, $this->escalations)) {
                $this->addError("could not find escalation with id '" . $escalationRef . "'", $escalationEventDefinitionElement, $parentElementId);
            } else {
                $escalation = $this->escalations[$escalationRef];
                $escalationEventDefinition->setEscalationCode($escalation->getEscalationCode());
            }
        }

        $escalationCodeVariable = $escalationEventDefinitionElement->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, "escalationCodeVariable");
        if ($escalationCodeVariable !== null) {
            $escalationEventDefinition->setEscalationCodeVariable($escalationCodeVariable);
        }

        return $escalationEventDefinition;
    }

    protected function addEscalationEventDefinition(ScopeImpl $catchingScope, EscalationEventDefinition $escalationEventDefinition, Element $element, ?string $escalationElementId): void
    {
        // ensure there is only one escalation handler (e.g. escalation boundary event, escalation event subprocess) what can catch the escalation event
        foreach ($catchingScope->getProperties()->get(BpmnProperties::escalationEventDefinitions()) as $existingEscalationEventDefinition) {
            if (
                $existingEscalationEventDefinition->getEscalationHandler()->isSubProcessScope()
                && $escalationEventDefinition->getEscalationHandler()->isSubProcessScope()
            ) {
                if ($existingEscalationEventDefinition->getEscalationCode() === null && $escalationEventDefinition->getEscalationCode() === null) {
                    $this->addError(
                        "The same scope can not contains more than one escalation event subprocess without escalation code. "
                        . "An escalation event subprocess without escalation code catch all escalation events.",
                        $element,
                        $escalationElementId
                    );
                } elseif ($existingEscalationEventDefinition->getEscalationCode() === null || $escalationEventDefinition->getEscalationCode() === null) {
                    $this->addError(
                        "The same scope can not contains an escalation event subprocess without escalation code and another one with escalation code. "
                        . "The escalation event subprocess without escalation code catch all escalation events.",
                        $element,
                        $escalationElementId
                    );
                } elseif ($existingEscalationEventDefinition->getEscalationCode() == $escalationEventDefinition->getEscalationCode()) {
                    $this->addError(
                        "multiple escalation event subprocesses with the same escalationCode '" . $escalationEventDefinition->getEscalationCode()
                        . "' are not supported on same scope",
                        $element,
                        $escalationElementId
                    );
                }
            } elseif (
                !$existingEscalationEventDefinition->getEscalationHandler()->isSubProcessScope()
                && !$escalationEventDefinition->getEscalationHandler()->isSubProcessScope()
            ) {
                if ($existingEscalationEventDefinition->getEscalationCode() === null && $escalationEventDefinition->getEscalationCode() === null) {
                    $this->addError(
                        "The same scope can not contains more than one escalation boundary event without escalation code. "
                        . "An escalation boundary event without escalation code catch all escalation events.",
                        $element,
                        $escalationElementId
                    );
                } elseif ($existingEscalationEventDefinition->getEscalationCode() === null || $escalationEventDefinition->getEscalationCode() === null) {
                    $this->addError(
                        "The same scope can not contains an escalation boundary event without escalation code and another one with escalation code. "
                        . "The escalation boundary event without escalation code catch all escalation events.",
                        $element,
                        $escalationElementId
                    );
                } elseif ($existingEscalationEventDefinition->getEscalationCode() == $escalationEventDefinition->getEscalationCode()) {
                    $this->addError(
                        "multiple escalation boundary events with the same escalationCode '" . $escalationEventDefinition->getEscalationCode()
                        . "' are not supported on same scope",
                        $element,
                        $escalationElementId
                    );
                }
            }
        }

        $catchingScope->getProperties()->addListItem(BpmnProperties::escalationEventDefinitions(), $escalationEventDefinition);
    }

    protected function addTimerDeclaration(ScopeImpl $scope, TimerDeclarationImpl $timerDeclaration): void
    {
        $scope->getProperties()->putMapEntry(BpmnProperties::timerDeclarations(), $timerDeclaration->getActivityId(), $timerDeclaration);
    }

    protected function addTimerListenerDeclaration(?string $listenerId, ScopeImpl $scope, TimerDeclarationImpl $timerDeclaration): void
    {
        $timeoutListenerDeclarations = $scope->getProperties()->get(BpmnProperties::timeoutListenerDeclarations());
        if (
            !empty($timeoutListenerDeclarations) && array_key_exists($timerDeclaration->getActivityId(), $timeoutListenerDeclarations)
        ) {
            $timeoutListenerDeclarations->addProperty($listenerId, $timerDeclaration);
        } else {
            $activityDeclarations = [];
            $activityDeclarations[$listenerId] = $timerDeclaration;
            $scope->getProperties()->putMapEntry(BpmnProperties::timeoutListenerDeclarations(), $timerDeclaration->getActivityId(), $activityDeclarations);
        }
    }

    protected function addVariableDeclaration(ScopeImpl $scope, VariableDeclaration $variableDeclaration): void
    {
        $variableDeclarations = $scope->getProperty(self::PROPERTYNAME_VARIABLE_DECLARATIONS);
        if (empty($variableDeclarations)) {
            $scope->clearProperty(self::PROPERTYNAME_VARIABLE_DECLARATIONS);
        }
        $scope->addProperty(self::PROPERTYNAME_VARIABLE_DECLARATIONS, $variableDeclaration);
    }

    /**
    * Parses the given element as conditional boundary event.
    *
    * @param element the XML element which contains the conditional event information
    * @param interrupting indicates if the event is interrupting or not
    * @param conditionalActivity the conditional event activity
    * @return BoundaryConditionalEventActivityBehavior the boundary conditional event behavior which contains the condition
    */
    public function parseBoundaryConditionalEventDefinition(Element $element, bool $interrupting, ActivityImpl $conditionalActivity): ?BoundaryConditionalEventActivityBehavior
    {
        $conditionalActivity->getProperties()->set(BpmnProperties::type(), ActivityTypes::BOUNDARY_CONDITIONAL);

        $conditionalEventDefinition = $this->parseConditionalEventDefinition($element, $conditionalActivity);
        $conditionalEventDefinition->setInterrupting($interrupting);
        $this->addEventSubscriptionDeclaration($conditionalEventDefinition, $conditionalActivity->getEventScope(), $element);

        foreach ($this->parseListeners as $parseListener) {
            $parseListener->parseBoundaryConditionalEventDefinition($element, $interrupting, $conditionalActivity);
        }

        return new BoundaryConditionalEventActivityBehavior($conditionalEventDefinition);
    }

    /**
    * Parses the given element as intermediate conditional event.
    *
    * @param element the XML element which contains the conditional event information
    * @param conditionalActivity the conditional event activity
    * @return returns the conditional activity with the parsed information
    */
    public function parseIntermediateConditionalEventDefinition(Element $element, ActivityImpl $conditionalActivity): ?ConditionalEventDefinition
    {
        $conditionalActivity->getProperties()->set(BpmnProperties::type(), ActivityTypes::INTERMEDIATE_EVENT_CONDITIONAL);

        $conditionalEventDefinition = $this->parseConditionalEventDefinition($element, $conditionalActivity);
        $this->addEventSubscriptionDeclaration($conditionalEventDefinition, $conditionalActivity->getEventScope(), $element);

        foreach ($this->parseListeners as $parseListener) {
            $parseListener->parseIntermediateConditionalEventDefinition($element, $conditionalActivity);
        }

        return $conditionalEventDefinition;
    }

    /**
    * Parses the given element as conditional start event of an event subprocess.
    *
    * @param element the XML element which contains the conditional event information
    * @param interrupting indicates if the event is interrupting or not
    * @param conditionalActivity the conditional event activity
    * @return
    */
    public function parseConditionalStartEventForEventSubprocess(Element $element, ActivityImpl $conditionalActivity, bool $interrupting): ?ConditionalEventDefinition
    {
        $conditionalActivity->getProperties()->set(BpmnProperties::type(), ActivityTypes::START_EVENT_CONDITIONAL);

        $conditionalEventDefinition = $this->parseConditionalEventDefinition($element, $conditionalActivity);
        $conditionalEventDefinition->setInterrupting($interrupting);
        $this->addEventSubscriptionDeclaration($conditionalEventDefinition, $conditionalActivity->getEventScope(), $element);

        foreach ($this->parseListeners as $parseListener) {
            $parseListener->parseConditionalStartEventForEventSubprocess($element, $conditionalActivity, $interrupting);
        }

        return $conditionalEventDefinition;
    }

    /**
    * Parses the given element and returns an ConditionalEventDefinition object.
    *
    * @param element the XML element which contains the conditional event information
    * @param conditionalActivity the conditional event activity
    * @return ConditionalEventDefinition the conditional event definition which was parsed
    */
    protected function parseConditionalEventDefinition(Element $element, ActivityImpl $conditionalActivity): ?ConditionalEventDefinition
    {
        $conditionalEventDefinition = null;

        $conditionExprElement = $element->element(self::CONDITION);
        $conditionalActivityId = $conditionalActivity->getId();
        if ($conditionExprElement !== null) {
            $condition = $this->parseConditionExpression($conditionExprElement, $conditionalActivityId);
            $conditionalEventDefinition = new ConditionalEventDefinition($condition, $conditionalActivity);

            $expression = trim($conditionExprElement->getText());
            $conditionalEventDefinition->setConditionAsString($expression);

            $conditionalActivity->getProcessDefinition()->getProperties()->set(BpmnProperties::hasConditionalEvents(), true);

            $variableName = $element->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, "variableName");
            $conditionalEventDefinition->setVariableName($variableName);

            $variableEvents = $element->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, "variableEvents");
            $variableEventsList = $this->parseCommaSeparatedList($variableEvents);
            $conditionalEventDefinition->setVariableEvents($variableEventsList);

            foreach ($variableEventsList as $variableEvent) {
                if (!in_array($variableEvent, self::VARIABLE_EVENTS)) {
                    $this->addWarning(
                        "Variable event: " . $variableEvent . " is not valid. Possible variable change events are: " . json_encode(self::VARIABLE_EVENTS),
                        $element,
                        $conditionalActivityId
                    );
                }
            }
        } else {
            $this->addError("Conditional event must contain an expression for evaluation.", $element, $conditionalActivityId);
        }

        return $conditionalEventDefinition;
    }

    /**
    * Parses a subprocess (formally known as an embedded subprocess): a
    * subprocess defined within another process definition.
    *
    * @param subProcessElement
    *          The XML element corresponding with the subprocess definition
    * @param scope
    *          The current scope on which the subprocess is defined.
    */
    public function parseSubProcess(Element $subProcessElement, ScopeImpl $scope): ?ActivityImpl
    {
        $subProcessActivity = $this->createActivityOnScope($subProcessElement, $scope);
        $subProcessActivity->setSubProcessScope(true);

        $this->parseAsynchronousContinuationForActivity($subProcessElement, $subProcessActivity);

        $isTriggeredByEvent = $this->parseBooleanAttribute($subProcessElement->attribute("triggeredByEvent"), false);
        $subProcessActivity->getProperties()->set(BpmnProperties::triggeredByEvent(), $isTriggeredByEvent);
        $subProcessActivity->setProperty(self::PROPERTYNAME_CONSUMES_COMPENSATION, !$isTriggeredByEvent);

        $subProcessActivity->setScope(true);
        if ($isTriggeredByEvent) {
            $subProcessActivity->setActivityBehavior(new EventSubProcessActivityBehavior());
            $subProcessActivity->setEventScope($scope);
        } else {
            $subProcessActivity->setActivityBehavior(new SubProcessActivityBehavior());
        }
        $this->parseScope($subProcessElement, $subProcessActivity);

        foreach ($this->parseListeners as $parseListener) {
            $parseListener->parseSubProcess($subProcessElement, $scope, $subProcessActivity);
        }
        return $subProcessActivity;
    }

    protected function parseTransaction(Element $transactionElement, ScopeImpl $scope): ?ActivityImpl
    {
        $activity = $this->createActivityOnScope($transactionElement, $scope);

        $this->parseAsynchronousContinuationForActivity($transactionElement, $activity);

        $activity->setScope(true);
        $activity->setSubProcessScope(true);
        $activity->setActivityBehavior(new SubProcessActivityBehavior());
        $activity->getProperties()->set(BpmnProperties::triggeredByEvent(), false);
        $this->parseScope($transactionElement, $activity);

        foreach ($this->parseListeners as $parseListener) {
            $parseListener->parseTransaction($transactionElement, $scope, $activity);
        }
        return $activity;
    }

    /**
    * Parses a call activity (currently only supporting calling subprocesses).
    *
    * @param callActivityElement
    *          The XML element defining the call activity
    * @param scope
    *          The current scope on which the call activity is defined.
    */
    public function parseCallActivity(Element $callActivityElement, ScopeImpl $scope, bool $isMultiInstance): ?ActivityImpl
    {
        $activity = $this->createActivityOnScope($callActivityElement, $scope);

        // parse async
        $this->parseAsynchronousContinuationForActivity($callActivityElement, $activity);

        // parse definition key (and behavior)
        $calledElement = $callActivityElement->attribute("calledElement");
        //$caseRef = $callActivityElement->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, "caseRef");
        $className = $callActivityElement->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, self::PROPERTYNAME_VARIABLE_MAPPING_CLASS);
        $delegateExpression = $callActivityElement->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, self::PROPERTYNAME_VARIABLE_MAPPING_DELEGATE_EXPRESSION);

        if ($calledElement === null) { // && caseRef === null
            $this->addError("Missing attribute 'calledElement' or 'caseRef'", callActivityElement);
        }/*elseif ($calledElement !== null) { // && caseRef !== null
            $this->addError("The attributes 'calledElement' or 'caseRef' cannot be used together: Use either 'calledElement' or 'caseRef'", $callActivityElement);
        }*/

        $bindingAttributeName = "calledElementBinding";
        $versionAttributeName = "calledElementVersion";
        $versionTagAttributeName = "calledElementVersionTag";
        $tenantIdAttributeName = "calledElementTenantId";

        $deploymentId = $this->deployment->getId();

        $callableElement = new CallableElement();
        $callableElement->setDeploymentId($deploymentId);

        $behavior = null;

        if ($calledElement !== null) {
            if ($className !== null) {
                $behavior = new CallActivityBehavior($className);
            } elseif ($delegateExpression !== null) {
                $exp = $this->expressionManager->createExpression($delegateExpression);
                $behavior = new CallActivityBehavior($exp);
            } else {
                $behavior = new CallActivityBehavior();
            }
            $definitionKeyProvider = $this->createParameterValueProvider($calledElement, $this->expressionManager);
            $callableElement->setDefinitionKeyValueProvider($definitionKeyProvider);
        } else {
            throw new \Exception("cmmn not yet implemented");
            /*$behavior = new CaseCallActivityBehavior();
            $definitionKeyProvider = $this->createParameterValueProvider($caseRef, $expressionManager);
            $callableElement->setDefinitionKeyValueProvider($definitionKeyProvider);
            $bindingAttributeName = "caseBinding";
            $versionAttributeName = "caseVersion";
            $tenantIdAttributeName = "caseTenantId";*/
        }

        $behavior->setCallableElement($callableElement);

        // parse binding
        $this->parseBinding($callActivityElement, $activity, $callableElement, $bindingAttributeName);

        // parse version
        $this->parseVersion($callActivityElement, $activity, $callableElement, $bindingAttributeName, $versionAttributeName);

        // parse versionTag
        $this->parseVersionTag($callActivityElement, $activity, $callableElement, $bindingAttributeName, $versionTagAttributeName);

        // parse tenant id
        $this->parseTenantId($callActivityElement, $activity, $callableElement, $tenantIdAttributeName);

        // parse input parameter
        $this->parseInputParameter($callActivityElement, $callableElement);

        // parse output parameter
        $this->parseOutputParameter($callActivityElement, $activity, $callableElement);

        if (!$isMultiInstance) {
            // turn activity into a scope unless it is a multi instance activity, in
            // that case this
            // is not necessary because there is already the multi instance body scope
            // and concurrent
            // child executions are sufficient
            $activity->setScope(true);
        }
        $activity->setActivityBehavior($behavior);

        $this->parseExecutionListenersOnScope($callActivityElement, $activity);

        foreach ($this->parseListeners as $parseListener) {
            $parseListener->parseCallActivity($callActivityElement, $scope, $activity);
        }
        return $activity;
    }

    protected function parseBinding(Element $callActivityElement, ActivityImpl $activity, BaseCallableElement $callableElement, ?string $bindingAttributeName): void
    {
        $binding = $callActivityElement->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, $bindingAttributeName);

        if (CallableElementBinding::DEPLOYMENT == $binding) {
            $callableElement->setBinding(CallableElementBinding::DEPLOYMENT);
        } elseif (CallableElementBinding::LATEST == $binding) {
            $callableElement->setBinding(CallableElementBinding::LATEST);
        } elseif (CallableElementBinding::VERSION == $binding) {
            $callableElement->setBinding(CallableElementBinding::VERSION);
        } elseif (CallableElementBinding::VERSION_TAG == $binding) {
            $callableElement->setBinding(CallableElementBinding::VERSION_TAG);
        }
    }

    protected function parseTenantId(Element $callingActivityElement, ActivityImpl $activity, BaseCallableElement $callableElement, ?string $attrName): void
    {
        $tenantIdValueProvider = null;

        $tenantId = $callingActivityElement->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, $attrName);
        if (!empty($tenantId)) {
            $tenantIdValueProvider = $this->createParameterValueProvider($tenantId, $this->expressionManager);
        }

        $callableElement->setTenantIdProvider($tenantIdValueProvider);
    }

    protected function parseVersion(Element $callingActivityElement, ActivityImpl $activity, BaseCallableElement $callableElement, ?string $bindingAttributeName, ?string $versionAttributeName): void
    {
        $version = null;

        $binding = $callableElement->getBinding();
        $version = $callingActivityElement->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, $versionAttributeName);

        if ($binding !== null && $binding == CallableElementBinding::VERSION && $version === null) {
            $this->addError("Missing attribute '" . $versionAttributeName . "' when '" . $bindingAttributeName . "' has value '" . CallableElementBinding::VERSION
            . "'", $callingActivityElement);
        }

        $versionProvider = $this->createParameterValueProvider($version, $this->expressionManager);
        $callableElement->setVersionValueProvider($versionProvider);
    }

    protected function parseVersionTag(Element $callingActivityElement, ActivityImpl $activity, BaseCallableElement $callableElement, ?string $bindingAttributeName, ?string $versionTagAttributeName): void
    {
        $versionTag = null;

        $binding = $callableElement->getBinding();
        $versionTag = $callingActivityElement->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, $versionTagAttributeName);

        if ($binding !== null && $binding == CallableElementBinding::VERSION_TAG && $versionTag === null) {
            $this->addError("Missing attribute '" . $versionTagAttributeName . "' when '" . $bindingAttributeName . "' has value '" . CallableElementBinding::VERSION_TAG . "'", $callingActivityElement);
        }

        $versionTagProvider = $this->createParameterValueProvider($versionTag, $this->expressionManager);
        $callableElement->setVersionTagValueProvider($versionTagProvider);
    }

    protected function parseInputParameter(Element $elementWithParameters, CallableElement $callableElement): void
    {
        $extensionsElement = $elementWithParameters->element("extensionElements");

        if (!empty($extensionsElement)) {
            // input data elements
            foreach ($extensionsElement->elements("in") as $inElement) {
                $businessKey = $inElement->attribute("businessKey");

                if ($businessKey !== null && !empty($businessKey)) {
                    $businessKeyValueProvider = $this->createParameterValueProvider($businessKey, $this->expressionManager);
                    $callableElement->setBusinessKeyValueProvider($businessKeyValueProvider);
                } else {
                    $parameter = $this->parseCallableElementProvider($inElement, $elementWithParameters->attribute("id"));

                    if ($this->attributeValueEquals($inElement, "local", "TRUE")) {
                        $parameter->setReadLocal(true);
                    }

                    $callableElement->addInput($parameter);
                }
            }
        }
    }

    protected function parseOutputParameter(Element $callActivityElement, ActivityImpl $activity, CallableElement $callableElement): void
    {
        $extensionsElement = $callActivityElement->element("extensionElements");

        if ($extensionsElement !== null) {
            // output data elements
            foreach ($extensionsElement->elements("out") as $outElement) {
                $parameter = $this->parseCallableElementProvider($outElement, $callActivityElement->attribute("id"));
                if ($this->attributeValueEquals($outElement, "local", "TRUE")) {
                    $callableElement->addOutputLocal($parameter);
                } else {
                    $callableElement->addOutput($parameter);
                }
            }
        }
    }

    protected function attributeValueEquals(Element $element, ?string $attribute, ?string $comparisonValue): bool
    {
        $value = $element->attribute($attribute);
        return $comparisonValue == $value;
    }

    protected function parseCallableElementProvider(Element $parameterElement, ?string $ancestorElementId): CallableElementParameter
    {
        $parameter = new CallableElementParameter();

        $variables = $parameterElement->attribute("variables");

        if (self::ALL == $variables) {
            $parameter->setAllVariables(true);
        } else {
            $strictValidation = !Context::getProcessEngineConfiguration()->getDisableStrictCallActivityValidation();

            $sourceValueProvider = new NullValueProvider();

            $source = $parameterElement->attribute("source");
            if ($source !== null) {
                if (!empty($source)) {
                    $sourceValueProvider = new ConstantValueProvider($source);
                } else {
                    if ($strictValidation) {
                        $this->addError("Empty attribute 'source' when passing variables", $parameterElement, $ancestorElementId);
                    } else {
                        $source = null;
                    }
                }
            }

            if ($source === null) {
                $source = $parameterElement->attribute("sourceExpression");

                if ($source !== null) {
                    if (!empty($source)) {
                        $expression = $this->expressionManager->createExpression($source);
                        $sourceValueProvider = new ElValueProvider($expression);
                    } elseif ($strictValidation) {
                        $this->addError("Empty attribute 'sourceExpression' when passing variables", $parameterElement, $ancestorElementId);
                    }
                }
            }

            if ($strictValidation && $source === null) {
                $this->addError("Missing parameter 'source' or 'sourceExpression' when passing variables", $parameterElement, $ancestorElementId);
            }

            $parameter->setSourceValueProvider($sourceValueProvider);

            $target = $parameterElement->attribute("target");
            if (($strictValidation || $source !== null && !empty($source)) && $target === null) {
                $this->addError("Missing attribute 'target' when attribute 'source' or 'sourceExpression' is set", $parameterElement, $ancestorElementId);
            } elseif ($strictValidation && $target !== null && empty($target)) {
                $this->addError("Empty attribute 'target' when attribute 'source' or 'sourceExpression' is set", $parameterElement, $ancestorElementId);
            }
            $parameter->setTarget($target);
        }

        return $parameter;
    }

    /**
    * Parses the properties of an element (if any) that can contain properties
    * (processes, activities, etc.)
    *
    * Returns true if property subelemens are found.
    *
    * @param element
    *          The element that can contain properties.
    * @param activity
    *          The activity where the property declaration is done.
    */
    public function parseProperties(Element $element, ActivityImpl $activity): void
    {
        $propertyElements = $element->elements("property");
        foreach ($propertyElements as $propertyElement) {
            $this->parseProperty($propertyElement, $activity);
        }
    }

    /**
    * Parses one property definition.
    *
    * @param propertyElement
    *          The 'property' element that defines how a property looks like and
    *          is handled.
    */
    public function parseProperty(Element $propertyElement, ActivityImpl $activity): void
    {
        $id = $propertyElement->attribute("id");
        $name = $propertyElement->attribute("name");

        // If name isn't given, use the id as name
        if ($name === null) {
            if ($id === null) {
                $this->addError("Invalid property usage on line " . $propertyElement->getLine() . ": no id or name specified.", $propertyElement, $activity->getId());
            } else {
                $name = $id;
            }
        }

        $type = null;
        $this->parsePropertyCustomExtensions($activity, $propertyElement, $name, $type);
    }

    /**
    * Parses the custom extensions for properties.
    *
    * @param activity
    *          The activity where the property declaration is done.
    * @param propertyElement
    *          The 'property' element defining the property.
    * @param propertyName
    *          The name of the property.
    * @param propertyType
    *          The type of the property.
    */
    public function parsePropertyCustomExtensions(ActivityImpl $activity, Element $propertyElement, ?string $propertyName, ?string $propertyType): void
    {

        if ($propertyType === null) {
            $type = $propertyElement->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, self::TYPE);
            $propertyType = $type !== null ? $type : "string"; // default is string
        }

        $variableDeclaration = new VariableDeclaration($propertyName, $propertyType);
        $this->addVariableDeclaration($activity, $variableDeclaration);
        $activity->setScope(true);

        $src = $propertyElement->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, "src");
        if ($src !== null) {
            $variableDeclaration->setSourceVariableName($src);
        }

        $srcExpr = $propertyElement->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, "srcExpr");
        if ($srcExpr !== null) {
            $sourceExpression = $this->expressionManager->createExpression($srcExpr);
            $variableDeclaration->setSourceExpression($sourceExpression);
        }

        $dst = $propertyElement->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, "dst");
        if ($dst !== null) {
            $variableDeclaration->setDestinationVariableName($dst);
        }

        $destExpr = $propertyElement->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, "dstExpr");
        if ($destExpr !== null) {
            $destinationExpression = $this->expressionManager->createExpression($destExpr);
            $variableDeclaration->setDestinationExpression($destinationExpression);
        }

        $link = $propertyElement->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, "link");
        if ($link !== null) {
            $variableDeclaration->setLink($link);
        }

        $linkExpr = $propertyElement->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, "linkExpr");
        if ($linkExpr !== null) {
            $linkExpression = $this->expressionManager->createExpression($linkExpr);
            $variableDeclaration->setLinkExpression($linkExpression);
        }

        foreach ($this->parseListeners as $parseListener) {
            $parseListener->parseProperty($propertyElement, $variableDeclaration, $activity);
        }
    }

    /**
    * Parses all sequence flow of a scope.
    *
    * @param processElement
    *          The 'process' element wherein the sequence flow are defined.
    * @param scope
    *          The scope to which the sequence flow must be added.
    * @param compensationHandlers
    */
    public function parseSequenceFlow(Element $processElement, ScopeImpl $scope, array $compensationHandlers): void
    {
        foreach ($processElement->elements("sequenceFlow") as $sequenceFlowElement) {
            $id = $sequenceFlowElement->attribute("id");
            $sourceRef = $sequenceFlowElement->attribute("sourceRef");
            $destinationRef = $sequenceFlowElement->attribute("targetRef");

            // check if destination is a throwing link event (event source) which mean
            // we have
            // to target the catching link event (event target) here:
            if (array_key_exists($destinationRef, $this->eventLinkSources)) {
                $linkName = $this->eventLinkSources[$destinationRef];
                if (!array_key_exists($linkName, $this->eventLinkTargets)) {
                    $this->addError(
                        "sequence flow points to link event source with name '" . $linkName
                        . "' but no event target with that name exists. Most probably your link events are not configured correctly.",
                        $sequenceFlowElement
                    );
                    return;
                }
                $destinationRef = $this->eventLinkTargets[$linkName];
                // Reminder: Maybe we should log a warning if we use intermediate link
                // events which are not used?
                // e.g. we have a catching event without the corresponding throwing one.
                // not done for the moment as it does not break executability
            }

            // Implicit check: sequence flow cannot cross (sub) process boundaries: we
            // don't do a processDefinition.findActivity here
            $sourceActivity = $scope->findActivityAtLevelOfSubprocess($sourceRef);
            $destinationActivity = $scope->findActivityAtLevelOfSubprocess($destinationRef);

            if (
                ($sourceActivity === null && array_key_exists($sourceRef, $compensationHandlers))
                || ($sourceActivity !== null && $sourceActivity->isCompensationHandler())
            ) {
                $this->addError(
                    "Invalid outgoing sequence flow of compensation activity '" . $sourceRef
                    . "'. A compensation activity should not have an incoming or outgoing sequence flow.",
                    $sequenceFlowElement,
                    $sourceRef,
                    $id
                );
            } elseif (
                ($destinationActivity === null && array_key_exists($destinationRef, $compensationHandlers))
                || ($destinationActivity !== null && $destinationActivity->isCompensationHandler())
            ) {
                $this->addError(
                    "Invalid incoming sequence flow of compensation activity '" . $destinationRef
                    . "'. A compensation activity should not have an incoming or outgoing sequence flow.",
                    $sequenceFlowElement,
                    $destinationRef,
                    $id
                );
            } elseif ($sourceActivity === null) {
                $this->addError("Invalid source '" . $sourceRef . "' of sequence flow '" . $id . "'", $sequenceFlowElement);
            } elseif ($destinationActivity === null) {
                $this->addError("Invalid destination '" . $destinationRef . "' of sequence flow '" . $id . "'", $sequenceFlowElement);
            } elseif ($sourceActivity->getActivityBehavior() instanceof EventBasedGatewayActivityBehavior) {
                // ignore
            } elseif (
                $destinationActivity->getActivityBehavior() instanceof IntermediateCatchEventActivityBehavior && ($destinationActivity->getEventScope() !== null)
                && ($destinationActivity->getEventScope()->getActivityBehavior() instanceof EventBasedGatewayActivityBehavior)
            ) {
                $this->addError(
                    "Invalid incoming sequenceflow for intermediateCatchEvent with id '" . $destinationActivity->getId() . "' connected to an event-based gateway.",
                    $sequenceFlowElement
                );
            } elseif (
                $sourceActivity->getActivityBehavior() instanceof SubProcessActivityBehavior
                && $sourceActivity->isTriggeredByEvent()
            ) {
                $this->addError("Invalid outgoing sequence flow of event subprocess", $sequenceFlowElement);
            } elseif (
                $destinationActivity->getActivityBehavior() instanceof SubProcessActivityBehavior
                && $destinationActivity->isTriggeredByEvent()
            ) {
                $this->addError("Invalid incoming sequence flow of event subprocess", $sequenceFlowElement);
            } else {
                if (($ret = $this->getMultiInstanceScope($sourceActivity)) !== null) {
                    $sourceActivity = $ret;
                }
                if (($ret = $this->getMultiInstanceScope($destinationActivity)) !== null) {
                    $destinationActivity = $ret;
                }

                $transition = $sourceActivity->createOutgoingTransition($id);
                $this->sequenceFlows[$id] = $transition;
                $transition->setProperty("name", $sequenceFlowElement->attribute("name"));
                $transition->setProperty("documentation", self::parseDocumentation($sequenceFlowElement));
                $transition->setDestination($destinationActivity);
                $this->parseSequenceFlowConditionExpression($sequenceFlowElement, $transition);
                $this->parseExecutionListenersOnTransition($sequenceFlowElement, $transition);

                foreach ($this->parseListeners as $parseListener) {
                    $parseListener->parseSequenceFlow($sequenceFlowElement, $scope, $transition);
                }
            }
        }
    }

    /**
    * Parses a condition expression on a sequence flow.
    *
    * @param seqFlowElement
    *          The 'sequenceFlow' element that can contain a condition.
    * @param seqFlow
    *          The sequenceFlow object representation to which the condition must
    *          be added.
    */
    public function parseSequenceFlowConditionExpression(Element $seqFlowElement, TransitionImpl $seqFlow): void
    {
        $conditionExprElement = $seqFlowElement->element(self::CONDITION_EXPRESSION);
        if ($conditionExprElement !== null) {
            $condition = $this->parseConditionExpression($conditionExprElement, $seqFlow->getId());
            $seqFlow->setProperty(self::PROPERTYNAME_CONDITION_TEXT, trim($conditionExprElement->getText()));
            $seqFlow->setProperty(self::PROPERTYNAME_CONDITION, $condition);
        }
    }

    protected function parseConditionExpression(Element $conditionExprElement, ?string $ancestorElementId): ?ConditionInterface
    {
        $expression = trim($conditionExprElement->getText());
        $type = $conditionExprElement->attributeNS(BpmnParser::XSI_NS, self::TYPE);
        $language = $conditionExprElement->attribute(self::PROPERTYNAME_LANGUAGE);
        $resource = $conditionExprElement->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, self::PROPERTYNAME_RESOURCE);
        if (!empty($type)) {
            $value = strpos($type, ":") !== false ? $this->resolveName($type) : BpmnParser::BPMN20_NS . ":" . $type;
            if ($value != self::ATTRIBUTEVALUE_T_FORMAL_EXPRESSION) {
                $this->addError("Invalid type, only tFormalExpression is currently supported", $conditionExprElement, $ancestorElementId);
            }
        }
        $condition = null;
        if ($language === null) {
            $condition = new UelExpressionCondition($this->expressionManager->createExpression($expression));
        } else {
            try {
                $script = ScriptUtil::getScript($language, $expression, $resource, $this->expressionManager);
                $condition = new ScriptCondition($script);
            } catch (ProcessEngineException $e) {
                $this->addError("Unable to process condition expression:" . $e->getMessage(), $conditionExprElement, $ancestorElementId);
            }
        }
        return $condition;
    }

    /**
    * Parses all execution-listeners on a scope.
    *
    * @param scopeElement
    *          the XML element containing the scope definition.
    * @param scope
    *          the scope to add the executionListeners to.
    */
    public function parseExecutionListenersOnScope(Element $scopeElement, ScopeImpl $scope): void
    {
        $extentionsElement = $scopeElement->element("extensionElements");
        $scopeElementId = $scopeElement->attribute("id");
        if (!empty($extentionsElement)) {
            //elementsNS => self::BPMN_EXTENSIONS_NS_PREFIX
            $listenerElements = $extentionsElement->elements("executionListener");
            foreach ($listenerElements as $listenerElement) {
                $eventName = $listenerElement->attribute("event");
                if ($this->isValidEventNameForScope($eventName, $listenerElement, $scopeElementId)) {
                    $listener = $this->parseExecutionListener($listenerElement, $scopeElementId);
                    if ($listener !== null) {
                        $scope->addExecutionListener($eventName, $listener);
                    }
                }
            }
        }
    }

    /**
    * Check if the given event name is valid. If not, an appropriate error is
    * added.
    */
    protected function isValidEventNameForScope(?string $eventName, Element $listenerElement, ?string $ancestorElementId): bool
    {
        if (!empty(trim($eventName))) {
            if ("start" == $eventName || "end" == $eventName) {
                return true;
            } else {
                $this->addError("Attribute 'event' must be one of {start|end}", $listenerElement, $ancestorElementId);
            }
        } else {
            $this->addError("Attribute 'event' is mandatory on listener", $listenerElement, $ancestorElementId);
        }
        return false;
    }

    public function parseExecutionListenersOnTransition(Element $activitiElement, TransitionImpl $activity): void
    {
        $extensionElements = $activitiElement->element("extensionElements");
        if (!empty($extensionElements)) {
            $listenerElements = $extensionElements->elements("executionListener");
            foreach ($listenerElements as $listenerElement) {
                $listener = $this->parseExecutionListener($listenerElement, $activity->getId());
                if ($listener !== null) {
                    // Since a transition only fires event 'take', we don't parse the
                    // event attribute, it is ignored
                    $activity->addExecutionListener($listener);
                }
            }
        }
    }

    /**
    * Parses an ExecutionListener implementation for the given
    * executionListener element.
    *
    * @param executionListenerElement
    *          the XML element containing the executionListener definition.
    */
    public function parseExecutionListener(Element $executionListenerElement, ?string $ancestorElementId): ?ExecutionListenerInterface
    {
        $executionListener = null;

        $className = str_replace('.', '\\', $executionListenerElement->attribute(self::PROPERTYNAME_CLASS));
        $expression = $executionListenerElement->attribute(self::PROPERTYNAME_EXPRESSION);
        $delegateExpression = $executionListenerElement->attribute(self::PROPERTYNAME_DELEGATE_EXPRESSION);
        $scriptElement = $executionListenerElement->elementNS(BpmnParser::BPMN_EXTENSIONS_NS, "script");
        if (!empty($className)) {
            if (empty($className)) {
                $this->addError("Attribute 'class' cannot be empty", $executionListenerElement, $ancestorElementId);
            } else {
                $executionListener = new ClassDelegateExecutionListener($className, $this->parseFieldDeclarations($executionListenerElement));
            }
        } elseif (!empty($expression)) {
            $executionListener = new ExpressionExecutionListener($this->expressionManager->createExpression($expression));
        } elseif (!empty($delegateExpression)) {
            if (empty($delegateExpression)) {
                $this->addError("Attribute 'delegateExpression' cannot be empty", $executionListenerElement, $ancestorElementId);
            } else {
                $executionListener = new DelegateExpressionExecutionListener($this->expressionManager->createExpression($delegateExpression), $this->parseFieldDeclarations($executionListenerElement));
            }
        } elseif ($scriptElement !== null) {
            try {
                $executableScript = BpmnParseUtil::parseScript($scriptElement);
                if ($executableScript !== null) {
                    $executionListener = new ScriptExecutionListener($executableScript);
                }
            } catch (BpmnParseException $e) {
                $this->addError($e, $ancestorElementId);
            }
        } else {
            $this->addError("Element 'class', 'expression', 'delegateExpression' or 'script' is mandatory on executionListener", $executionListenerElement, $ancestorElementId);
        }
        return $executionListener;
    }

    // Diagram interchange
    // /////////////////////////////////////////////////////////////////

    public function parseDiagramInterchangeElements(): void
    {
        // Multiple BPMNDiagram possible
        $diagrams = $this->rootElement->elementsNS(self::BPMN_DI_NS, "BPMNDiagram");
        if (!empty($diagrams)) {
            foreach ($diagrams as $diagramElement) {
                $this->parseBPMNDiagram($diagramElement);
            }
        }
    }

    public function parseBPMNDiagram(Element $bpmndiagramElement): void
    {
        // Each BPMNdiagram needs to have exactly one BPMNPlane
        $bpmnPlane = $bpmndiagramElement->elementNS(self::BPMN_DI_NS, "BPMNPlane");
        if (!empty($bpmnPlane)) {
            $this->parseBPMNPlane($bpmnPlane);
        }
    }

    public function parseBPMNPlane(Element $bpmnPlaneElement): void
    {
        $bpmnElement = $bpmnPlaneElement->attribute("bpmnElement");
        if (!empty($bpmnElement)) {
            // there seems to be only on process without collaboration
            if ($this->getProcessDefinition($bpmnElement) !== null) {
                $this->getProcessDefinition($bpmnElement)->setGraphicalNotationDefined(true);
            }

            $shapes = $bpmnPlaneElement->elementsNS(self::BPMN_DI_NS, "BPMNShape");
            foreach ($shapes as $shape) {
                $this->parseBPMNShape($shape);
            }

            $edges = $bpmnPlaneElement->elementsNS(self::BPMN_DI_NS, "BPMNEdge");
            foreach ($edges as $edge) {
                $this->parseBPMNEdge($edge);
            }
        } else {
            $this->addError("'bpmnElement' attribute is required on BPMNPlane ", $bpmnPlaneElement);
        }
    }

    public function parseBPMNShape(Element $bpmnShapeElement): void
    {
        $bpmnElement = $bpmnShapeElement->attribute("bpmnElement");

        if (!empty($bpmnElement)) {
            // For collaborations, their are also shape definitions for the
            // participants / processes
            if (array_key_exists($bpmnElement, $this->participantProcesses)) {
                $procDef = $this->getProcessDefinition($this->participantProcesses[$bpmnElement]);
                $procDef->setGraphicalNotationDefined(true);

                // The participation that references this process, has a bounds to be
                // rendered + a name as wel
                $this->parseDIBounds($bpmnShapeElement, $procDef->getParticipantProcess());
                return;
            }

            foreach ($this->getProcessDefinitions() as $processDefinition) {
                $activity = $processDefinition->findActivity($bpmnElement);
                if ($activity !== null) {
                    $this->parseDIBounds($bpmnShapeElement, $activity);

                    // collapsed or expanded
                    $isExpanded = $bpmnShapeElement->attribute("isExpanded");
                    if ($isExpanded !== null) {
                        $activity->setProperty(self::PROPERTYNAME_ISEXPANDED, $this->parseBooleanAttribute($isExpanded));
                    }
                } else {
                    $lane = $processDefinition->getLaneForId($bpmnElement);

                    if ($lane !== null) {
                        // The shape represents a lane
                        $this->parseDIBounds($bpmnShapeElement, $lane);
                    } elseif (!in_array($bpmnElement, $this->elementIds)) { // It might not be an
                                                                    // activity nor a
                                                                    // lane, but it might
                                                                    // still reference
                                                                    // 'something'
                        $this->addError("Invalid reference in 'bpmnElement' attribute, $activity " . $bpmnElement . " not found", $bpmnShapeElement);
                    }
                }
            }
        } else {
            $this->addError("'bpmnElement' attribute is required on BPMNShape", $bpmnShapeElement);
        }
    }

    protected function parseDIBounds(Element $bpmnShapeElement, HasDIBoundsInterface $target): void
    {
        $bounds = $bpmnShapeElement->elementNS(self::BPMN_DC_NS, "Bounds");
        if (!empty($bounds)) {
            $target->setX(intval($this->parseDoubleAttribute($bpmnShapeElement, "x", $bounds->attribute("x"), true)));
            $target->setY(intval($this->parseDoubleAttribute($bpmnShapeElement, "y", $bounds->attribute("y"), true)));
            $target->setWidth(intval($this->parseDoubleAttribute($bpmnShapeElement, "width", $bounds->attribute("width"), true)));
            $target->setHeight(intval($this->parseDoubleAttribute($bpmnShapeElement, "height", $bounds->attribute("height"), true)));
        } else {
            $this->addError("'Bounds' element is required", $bpmnShapeElement);
        }
    }

    public function parseBPMNEdge(Element $bpmnEdgeElement): void
    {
        $sequenceFlowId = $bpmnEdgeElement->attribute("bpmnElement");
        if (!empty($sequenceFlowId)) {
            if (!empty($this->sequenceFlows) && array_key_exists($sequenceFlowId, $this->sequenceFlows)) {
                $sequenceFlow = $this->sequenceFlows[$sequenceFlowId];
                $waypointElements = $bpmnEdgeElement->elementsNS(self::OMG_DI_NS, "waypoint");
                if (count($waypointElements) >= 2) {
                    $waypoints = [];
                    foreach ($waypointElements as $waypointElement) {
                        $waypoints[] = intval($this->parseDoubleAttribute($waypointElement, "x", $waypointElement->attribute("x"), true));
                        $waypoints[] = intval($this->parseDoubleAttribute($waypointElement, "y", $waypointElement->attribute("y"), true));
                    }
                    $sequenceFlow->setWaypoints($waypoints);
                } else {
                    $this->addError("Minimum 2 waypoint elements must be definted for a 'BPMNEdge'", bpmnEdgeElement);
                }
            } elseif (!in_array($sequenceFlowId, $this->elementIds)) { // it might not be a
                                                                // sequenceFlow but it
                                                                // might still
                                                                // reference
                                                                // 'something'
                $this->addError("Invalid reference in 'bpmnElement' attribute, sequenceFlow " . $sequenceFlowId . "not found", $bpmnEdgeElement);
            }
        } else {
            $this->addError("'bpmnElement' attribute is required on BPMNEdge", $bpmnEdgeElement);
        }
    }

    // Getters, setters and Parser overridden operations
    // ////////////////////////////////////////

    public function getProcessDefinitions(): array
    {
        return $this->processDefinitions;
    }

    public function getProcessDefinition(?string $processDefinitionKey): ?ProcessDefinitionEntity
    {
        foreach ($this->processDefinitions as $processDefinition) {
            if ($processDefinition->getKey() == $processDefinitionKey) {
                return $processDefinition;
            }
        }
        return null;
    }

    public function name(?string $name): BpmnParse
    {
        parent::name($name);
        return $this;
    }

    public function sourceInputStream($inputStream): BpmnParse
    {
        parent::sourceInputStream($inputStream);
        return $this;
    }

    public function sourceResource(?string $resource): BpmnParse
    {
        parent::sourceResource($resource);
        return $this;
    }

    public function sourceString(?string $string): BpmnParse
    {
        parent::sourceString($string);
        return $this;
    }

    public function sourceUrl(?string $url): BpmnParse
    {
        parent::sourceUrl($url);
        return $this;
    }

    public function parseBooleanAttribute(?string $booleanText, ?bool $defaultValue = null): bool
    {
        if ($booleanText === null && $defaultValue !== null) {
            return $defaultValue;
        }
        $trueValues = ['true', 'enabled', 'on', 'active', 'yes'];
        if ($booleanText === true || in_array(strtolower($booleanText), $trueValues)) {
            return true;
        }
        return false;
    }

    public function parseDoubleAttribute(Element $element, ?string $attributeName, ?string $doubleText, bool $required): float
    {
        if ($required && empty($doubleText)) {
            $this->addError($attributeName . " is required", $element);
        } else {
            try {
                return floatval($doubleText);
            } catch (\Exception $e) {
                $this->addError("Cannot parse " . $attributeName . ": " . $e->getMessage(), $element);
            }
        }
        return -1.0;
    }

    protected function isStartable(Element $element): bool
    {
        $isStartableInTasklist = $element->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, "isStartableInTasklist", "true");
        return $isStartableInTasklist !== null && strtolower($isStartableInTasklist) === "true";
    }

    protected function isExclusive(Element $element): bool
    {
        $exclusive = $element->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, "exclusive", JobEntity::DEFAULT_EXCLUSIVE === true ? "true" : "false");
        return $exclusive !== null && strtolower($exclusive) === "true";
    }

    protected function isAsyncBefore(Element $element): bool
    {
        $async = $element->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, "async");
        $asyncBefore = $element->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, "asyncBefore");
        return ($async !== null && strtolower($async) === "true")
            || ($asyncBefore !== null && strtolower($asyncBefore) === "true");
    }

    protected function isAsyncAfter(Element $element): bool
    {
        $asyncAfter = $element->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, "asyncAfter");
        return $asyncAfter !== null && strtolower($asyncAfter) === "true";
    }

    protected function isServiceTaskLike(?Element $element): bool
    {
        return $element !== null && (
            $element->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, self::PROPERTYNAME_CLASS) !== null
            || $element->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, self::PROPERTYNAME_EXPRESSION) !== null
            || $element->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, self::PROPERTYNAME_DELEGATE_EXPRESSION) !== null
            || $element->attributeNS(self::BPMN_EXTENSIONS_NS_PREFIX, self::TYPE) !== null
            || $this->hasConnector($element)
        );
    }

    protected function hasConnector(Element $element): bool
    {
        $extensionElements = $element->element("extensionElements");
        return !empty($extensionElements) && $extensionElements->element("connector") !== null;
    }

    public function getJobDeclarations(): array
    {
        return $this->jobDeclarations;
    }

    public function getJobDeclarationsByKey(?string $processDefinitionKey): array
    {
        if (array_key_exists($processDefinitionKey, $this->jobDeclarations)) {
            return $this->jobDeclarations[$processDefinitionKey];
        }
        return [];
    }

    // IoMappings ////////////////////////////////////////////////////////

    protected function parseActivityInputOutput(Element $activityElement, ActivityImpl $activity): void
    {
        $extensionElements = $activityElement->element("extensionElements");
        if ($extensionElements !== null) {
            $inputOutput = null;
            try {
                $inputOutput = BpmnParseUtil::parseInputOutput($extensionElements);
            } catch (BpmnParseException $e) {
                $this->addError($e, $activity->getId());
            }

            if (!empty($inputOutput)) {
                if ($this->checkActivityInputOutputSupported($activityElement, $activity, $inputOutput)) {
                    $activity->setIoMapping($inputOutput);

                    if ($this->getMultiInstanceScope($activity) === null) {
                        // turn activity into a scope (->local, isolated scope for
                        // variables) unless it is a multi instance activity, in that case
                        // this
                        // is not necessary because:
                        // A scope is already created for the multi instance body which
                        // isolates the local variables from other executions in the same
                        // scope, and
                        // * parallel: the individual concurrent executions are isolated
                        // even if they are not scope themselves
                        // * sequential: after each iteration local variables are purged
                        $activity->setScope(true);
                    }
                }
            }
        }
    }

    protected function checkActivityInputOutputSupported(Element $activityElement, ActivityImpl $activity, IoMapping $inputOutput): bool
    {
        $tagName = strtoupper($activityElement->getTagName());

        if (
            !(
                strpos($tagName, "TASK") !== false
                || strpos($tagName, "EVENT") !== false
                || $tagName == "TRANSACTION"
                || $tagName == "SUBPROCESS"
                || $tagName == "CALLACTIVITY"
            )
        ) {
            $this->addError("extension:inputOutput mapping unsupported for element type '" . $tagName . "'.", $activityElement);
            return false;
        }
        $triggeredByEvent = $activityElement->attribute("triggeredByEvent");
        if ($tagName == "SUBPROCESS" && ($triggeredByEvent !== null && strtolower($triggeredByEvent) === "true")) {
            $this->addError("extension:inputOutput mapping unsupported for element type '" . $tagName . "' with attribute 'triggeredByEvent = true'.", $activityElement);
            return false;
        }

        if (!empty($inputOutput->getOutputParameters())) {
            return $this->checkActivityOutputParameterSupported($activityElement, $activity);
        } else {
            return true;
        }
    }

    protected function checkActivityOutputParameterSupported(Element $activityElement, ActivityImpl $activity): bool
    {
        $tagName = strtoupper($activityElement->getTagName());

        if ($tagName == "ENDEVENT") {
            $this->addError("extension:outputParameter not allowed for element type '" . $tagName . "'.", $activityElement);
            return true;
        } elseif ($this->getMultiInstanceScope($activity) !== null) {
            $this->addError("extension:outputParameter not allowed for multi-instance constructs", $activityElement);
            return false;
        } else {
            return true;
        }
    }

    protected function ensureNoIoMappingDefined(Element $element): void
    {
        $inputOutput = BpmnParseUtil::findExtensionElement($element, "inputOutput");
        if ($inputOutput !== null) {
            $this->addError("extension:inputOutput mapping unsupported for element type '" . $element->getTagName() . "'.", $element);
        }
    }

    protected function createParameterValueProvider($value, ExpressionManagerInterface $expressionManager): ParameterValueProviderInterface
    {
        if ($value === null) {
            return new NullValueProvider();
        } elseif (is_string($value)) {
            $expression = $expressionManager->createExpression($value);
            return new ElValueProvider($expression);
        } else {
            return new ConstantValueProvider($value);
        }
    }

    protected function addTimeCycleWarning(Element $timeCycleElement, ?string $type, ?string $timerElementId): void
    {
        $warning = "It is not recommended to use a " . $type . " timer event with a time cycle.";
        $this->addWarning($warning, $timeCycleElement, $timerElementId);
    }

    protected function ensureNoExpressionInMessageStartEvent(
        Element $element,
        EventSubscriptionDeclaration $messageStartEventSubscriptionDeclaration,
        ?string $parentElementId
    ): void {
        $eventNameContainsExpression = false;
        if ($messageStartEventSubscriptionDeclaration->hasEventName()) {
            $eventNameContainsExpression = !$messageStartEventSubscriptionDeclaration->isEventNameLiteralText();
        }
        if ($eventNameContainsExpression) {
            $messageStartName = $messageStartEventSubscriptionDeclaration->getUnresolvedEventName();
            $this->addError("Invalid message name '" . $messageStartName . "' for element '" .
                $element->getTagName() . "': expressions in the message start event name are not allowed!", $element, $parentElementId);
        }
    }
}
