<?php

namespace BpmPlatform\Model\Bpmn\Impl;

class BpmnModelConstants
{
    /** The XSI namespace */
    public const XSI_NS = "http://www.w3.org/2001/XMLSchema-instance";

    /** The BPMN 2.0 namespace */
    public const BPMN20_NS = "http://www.omg.org/spec/BPMN/20100524/MODEL";

    /** The BPMNDI namespace */
    public const BPMNDI_NS = "http://www.omg.org/spec/BPMN/20100524/DI";

    /** The DC namespace */
    public const DC_NS = "http://www.omg.org/spec/DD/20100524/DC";

    /** The DI namespace */
    public const DI_NS = "http://www.omg.org/spec/DD/20100524/DI";

    /** The location of the BPMN 2.0 XML schema. */
    public const BPMN_20_SCHEMA_LOCATION = "src/Engine/Resources/Bpmn/BPMN20.xsd";

    /** Xml Schema is the default type language */
    public const XML_SCHEMA_NS = "http://www.w3.org/2001/XMLSchema";

    public const XPATH_NS = "http://www.w3.org/1999/XPath";

    /**
     * @deprecated use {@link #EXTENSION_NS}
     */
    public const ACTIVITI_NS = "http://activiti.org/bpmn";

    /** EXTENSION_NS namespace */
    public const EXTENSION_NS = "http://test.org/schema/1.0/bpmn";

    // elements ////////////////////////////////////////

    public const BPMN_ELEMENT_BASE_ELEMENT = "baseElement";
    public const BPMN_ELEMENT_DEFINITIONS = "definitions";
    public const BPMN_ELEMENT_DOCUMENTATION = "documentation";
    public const BPMN_ELEMENT_EXTENSION = "extension";
    public const BPMN_ELEMENT_EXTENSION_ELEMENTS = "extensionElements";
    public const BPMN_ELEMENT_IMPORT = "import";
    public const BPMN_ELEMENT_RELATIONSHIP = "relationship";
    public const BPMN_ELEMENT_SOURCE = "source";
    public const BPMN_ELEMENT_TARGET = "target";
    public const BPMN_ELEMENT_ROOT_ELEMENT = "rootElement";
    public const BPMN_ELEMENT_AUDITING = "auditing";
    public const BPMN_ELEMENT_MONITORING = "monitoring";
    public const BPMN_ELEMENT_CATEGORY_VALUE = "categoryValue";
    public const BPMN_ELEMENT_FLOW_ELEMENT = "flowElement";
    public const BPMN_ELEMENT_FLOW_NODE = "flowNode";
    public const BPMN_ELEMENT_CATEGORY_VALUE_REF = "categoryValueRef";
    public const BPMN_ELEMENT_EXPRESSION = "expression";
    public const BPMN_ELEMENT_CONDITION_EXPRESSION = "conditionExpression";
    public const BPMN_ELEMENT_SEQUENCE_FLOW = "sequenceFlow";
    public const BPMN_ELEMENT_INCOMING = "incoming";
    public const BPMN_ELEMENT_OUTGOING = "outgoing";
    public const BPMN_ELEMENT_DATA_STATE = "dataState";
    public const BPMN_ELEMENT_ITEM_DEFINITION = "itemDefinition";
    public const BPMN_ELEMENT_ERROR = "error";
    public const BPMN_ELEMENT_IN_MESSAGE_REF = "inMessageRef";
    public const BPMN_ELEMENT_OUT_MESSAGE_REF = "outMessageRef";
    public const BPMN_ELEMENT_ERROR_REF = "errorRef";
    public const BPMN_ELEMENT_OPERATION = "operation";
    public const BPMN_ELEMENT_IMPLEMENTATION_REF = "implementationRef";
    public const BPMN_ELEMENT_OPERATION_REF = "operationRef";
    public const BPMN_ELEMENT_DATA_OUTPUT = "dataOutput";
    public const BPMN_ELEMENT_FROM = "from";
    public const BPMN_ELEMENT_TO = "to";
    public const BPMN_ELEMENT_ASSIGNMENT = "assignment";
    public const BPMN_ELEMENT_ITEM_AWARE_ELEMENT = "itemAwareElement";
    public const BPMN_ELEMENT_DATA_OBJECT = "dataObject";
    public const BPMN_ELEMENT_DATA_OBJECT_REFERENCE = "dataObjectReference";
    public const BPMN_ELEMENT_DATA_STORE = "dataStore";
    public const BPMN_ELEMENT_DATA_STORE_REFERENCE = "dataStoreReference";
    public const BPMN_ELEMENT_DATA_INPUT = "dataInput";
    public const BPMN_ELEMENT_FORMAL_EXPRESSION = "formalExpression";
    public const BPMN_ELEMENT_DATA_ASSOCIATION = "dataAssociation";
    public const BPMN_ELEMENT_SOURCE_REF = "sourceRef";
    public const BPMN_ELEMENT_TARGET_REF = "targetRef";
    public const BPMN_ELEMENT_TRANSFORMATION = "transformation";
    public const BPMN_ELEMENT_DATA_INPUT_ASSOCIATION = "dataInputAssociation";
    public const BPMN_ELEMENT_DATA_OUTPUT_ASSOCIATION = "dataOutputAssociation";
    public const BPMN_ELEMENT_INPUT_SET = "inputSet";
    public const BPMN_ELEMENT_OUTPUT_SET = "outputSet";
    public const BPMN_ELEMENT_DATA_INPUT_REFS = "dataInputRefs";
    public const BPMN_ELEMENT_OPTIONAL_INPUT_REFS = "optionalInputRefs";
    public const BPMN_ELEMENT_WHILE_EXECUTING_INPUT_REFS = "whileExecutingInputRefs";
    public const BPMN_ELEMENT_OUTPUT_SET_REFS = "outputSetRefs";
    public const BPMN_ELEMENT_DATA_OUTPUT_REFS = "dataOutputRefs";
    public const BPMN_ELEMENT_OPTIONAL_OUTPUT_REFS = "optionalOutputRefs";
    public const BPMN_ELEMENT_WHILE_EXECUTING_OUTPUT_REFS = "whileExecutingOutputRefs";
    public const BPMN_ELEMENT_INPUT_SET_REFS = "inputSetRefs";
    public const BPMN_ELEMENT_CATCH_EVENT = "catchEvent";
    public const BPMN_ELEMENT_THROW_EVENT = "throwEvent";
    public const BPMN_ELEMENT_END_EVENT = "endEvent";
    public const BPMN_ELEMENT_IO_SPECIFICATION = "ioSpecification";
    public const BPMN_ELEMENT_LOOP_CHARACTERISTICS = "loopCharacteristics";
    public const BPMN_ELEMENT_RESOURCE_PARAMETER = "resourceParameter";
    public const BPMN_ELEMENT_RESOURCE = "resource";
    public const BPMN_ELEMENT_RESOURCE_PARAMETER_BINDING = "resourceParameterBinding";
    public const BPMN_ELEMENT_RESOURCE_ASSIGNMENT_EXPRESSION = "resourceAssignmentExpression";
    public const BPMN_ELEMENT_RESOURCE_ROLE = "resourceRole";
    public const BPMN_ELEMENT_RESOURCE_REF = "resourceRef";
    public const BPMN_ELEMENT_PERFORMER = "performer";
    public const BPMN_ELEMENT_HUMAN_PERFORMER = "humanPerformer";
    public const BPMN_ELEMENT_POTENTIAL_OWNER = "potentialOwner";
    public const BPMN_ELEMENT_ACTIVITY = "activity";
    public const BPMN_ELEMENT_IO_BINDING = "ioBinding";
    public const BPMN_ELEMENT_INTERFACE = "interface";
    public const BPMN_ELEMENT_EVENT = "event";
    public const BPMN_ELEMENT_MESSAGE = "message";
    public const BPMN_ELEMENT_START_EVENT = "startEvent";
    public const BPMN_ELEMENT_PROPERTY = "property";
    public const BPMN_ELEMENT_EVENT_DEFINITION = "eventDefinition";
    public const BPMN_ELEMENT_EVENT_DEFINITION_REF = "eventDefinitionRef";
    public const BPMN_ELEMENT_MESSAGE_EVENT_DEFINITION = "messageEventDefinition";
    public const BPMN_ELEMENT_CANCEL_EVENT_DEFINITION = "cancelEventDefinition";
    public const BPMN_ELEMENT_COMPENSATE_EVENT_DEFINITION = "compensateEventDefinition";
    public const BPMN_ELEMENT_CONDITIONAL_EVENT_DEFINITION = "conditionalEventDefinition";
    public const BPMN_ELEMENT_CONDITION = "condition";
    public const BPMN_ELEMENT_ERROR_EVENT_DEFINITION = "errorEventDefinition";
    public const BPMN_ELEMENT_LINK_EVENT_DEFINITION = "linkEventDefinition";
    public const BPMN_ELEMENT_SIGNAL_EVENT_DEFINITION = "signalEventDefinition";
    public const BPMN_ELEMENT_TERMINATE_EVENT_DEFINITION = "terminateEventDefinition";
    public const BPMN_ELEMENT_TIMER_EVENT_DEFINITION = "timerEventDefinition";
    public const BPMN_ELEMENT_SUPPORTED_INTERFACE_REF = "supportedInterfaceRef";
    public const BPMN_ELEMENT_CALLABLE_ELEMENT = "callableElement";
    public const BPMN_ELEMENT_PARTITION_ELEMENT = "partitionElement";
    public const BPMN_ELEMENT_FLOW_NODE_REF = "flowNodeRef";
    public const BPMN_ELEMENT_CHILD_LANE_SET = "childLaneSet";
    public const BPMN_ELEMENT_LANE_SET = "laneSet";
    public const BPMN_ELEMENT_LANE = "lane";
    public const BPMN_ELEMENT_ARTIFACT = "artifact";
    public const BPMN_ELEMENT_CORRELATION_PROPERTY_RETRIEVAL_EXPRESSION = "correlationPropertyRetrievalExpression";
    public const BPMN_ELEMENT_MESSAGE_PATH = "messagePath";
    public const BPMN_ELEMENT_DATA_PATH = "dataPath";
    public const BPMN_ELEMENT_CALL_ACTIVITY = "callActivity";
    public const BPMN_ELEMENT_CORRELATION_PROPERTY_BINDING = "correlationPropertyBinding";
    public const BPMN_ELEMENT_CORRELATION_PROPERTY = "correlationProperty";
    public const BPMN_ELEMENT_CORRELATION_PROPERTY_REF = "correlationPropertyRef";
    public const BPMN_ELEMENT_CORRELATION_KEY = "correlationKey";
    public const BPMN_ELEMENT_CORRELATION_SUBSCRIPTION = "correlationSubscription";
    public const BPMN_ELEMENT_SUPPORTS = "supports";
    public const BPMN_ELEMENT_PROCESS = "process";
    public const BPMN_ELEMENT_TASK = "task";
    public const BPMN_ELEMENT_SEND_TASK = "sendTask";
    public const BPMN_ELEMENT_SERVICE_TASK = "serviceTask";
    public const BPMN_ELEMENT_SCRIPT_TASK = "scriptTask";
    public const BPMN_ELEMENT_USER_TASK = "userTask";
    public const BPMN_ELEMENT_RECEIVE_TASK = "receiveTask";
    public const BPMN_ELEMENT_BUSINESS_RULE_TASK = "businessRuleTask";
    public const BPMN_ELEMENT_MANUAL_TASK = "manualTask";
    public const BPMN_ELEMENT_SCRIPT = "script";
    public const BPMN_ELEMENT_RENDERING = "rendering";
    public const BPMN_ELEMENT_BOUNDARY_EVENT = "boundaryEvent";
    public const BPMN_ELEMENT_SUB_PROCESS = "subProcess";
    public const BPMN_ELEMENT_TRANSACTION = "transaction";
    public const BPMN_ELEMENT_GATEWAY = "gateway";
    public const BPMN_ELEMENT_PARALLEL_GATEWAY = "parallelGateway";
    public const BPMN_ELEMENT_EXCLUSIVE_GATEWAY = "exclusiveGateway";
    public const BPMN_ELEMENT_INTERMEDIATE_CATCH_EVENT = "intermediateCatchEvent";
    public const BPMN_ELEMENT_INTERMEDIATE_THROW_EVENT = "intermediateThrowEvent";
    public const BPMN_ELEMENT_END_POINT = "endPoint";
    public const BPMN_ELEMENT_PARTICIPANT_MULTIPLICITY = "participantMultiplicity";
    public const BPMN_ELEMENT_PARTICIPANT = "participant";
    public const BPMN_ELEMENT_PARTICIPANT_REF = "participantRef";
    public const BPMN_ELEMENT_INTERFACE_REF = "interfaceRef";
    public const BPMN_ELEMENT_END_POINT_REF = "endPointRef";
    public const BPMN_ELEMENT_MESSAGE_FLOW = "messageFlow";
    public const BPMN_ELEMENT_MESSAGE_FLOW_REF = "messageFlowRef";
    public const BPMN_ELEMENT_CONVERSATION_NODE = "conversationNode";
    public const BPMN_ELEMENT_CONVERSATION = "conversation";
    public const BPMN_ELEMENT_SUB_CONVERSATION = "subConversation";
    public const BPMN_ELEMENT_GLOBAL_CONVERSATION = "globalConversation";
    public const BPMN_ELEMENT_CALL_CONVERSATION = "callConversation";
    public const BPMN_ELEMENT_PARTICIPANT_ASSOCIATION = "participantAssociation";
    public const BPMN_ELEMENT_INNER_PARTICIPANT_REF = "innerParticipantRef";
    public const BPMN_ELEMENT_OUTER_PARTICIPANT_REF = "outerParticipantRef";
    public const BPMN_ELEMENT_CONVERSATION_ASSOCIATION = "conversationAssociation";
    public const BPMN_ELEMENT_MESSAGE_FLOW_ASSOCIATION = "messageFlowAssociation";
    public const BPMN_ELEMENT_CONVERSATION_LINK = "conversationLink";
    public const BPMN_ELEMENT_COLLABORATION = "collaboration";
    public const BPMN_ELEMENT_ASSOCIATION = "association";
    public const BPMN_ELEMENT_SIGNAL = "signal";
    public const BPMN_ELEMENT_TIME_DATE = "timeDate";
    public const BPMN_ELEMENT_TIME_DURATION = "timeDuration";
    public const BPMN_ELEMENT_TIME_CYCLE = "timeCycle";
    public const BPMN_ELEMENT_ESCALATION = "escalation";
    public const BPMN_ELEMENT_ESCALATION_EVENT_DEFINITION = "escalationEventDefinition";
    public const BPMN_ELEMENT_ACTIVATION_CONDITION = "activationCondition";
    public const BPMN_ELEMENT_COMPLEX_GATEWAY = "complexGateway";
    public const BPMN_ELEMENT_EVENT_BASED_GATEWAY = "eventBasedGateway";
    public const BPMN_ELEMENT_INCLUSIVE_GATEWAY = "inclusiveGateway";
    public const BPMN_ELEMENT_TEXT_ANNOTATION = "textAnnotation";
    public const BPMN_ELEMENT_TEXT = "text";
    public const BPMN_ELEMENT_COMPLEX_BEHAVIOR_DEFINITION = "complexBehaviorDefinition";
    public const BPMN_ELEMENT_MULTI_INSTANCE_LOOP_CHARACTERISTICS = "multiInstanceLoopCharacteristics";
    public const BPMN_ELEMENT_LOOP_CARDINALITY = "loopCardinality";
    public const BPMN_ELEMENT_COMPLETION_CONDITION = "completionCondition";
    public const BPMN_ELEMENT_OUTPUT_DATA_ITEM = "outputDataItem";
    public const BPMN_ELEMENT_INPUT_DATA_ITEM = "inputDataItem";
    public const BPMN_ELEMENT_LOOP_DATA_OUTPUT_REF = "loopDataOutputRef";
    public const BPMN_ELEMENT_LOOP_DATA_INPUT_REF = "loopDataInputRef";
    public const BPMN_ELEMENT_IS_SEQUENTIAL = "isSequential";
    public const BPMN_ELEMENT_BEHAVIOR = "behavior";
    public const BPMN_ELEMENT_ONE_BEHAVIOR_EVENT_REF = "oneBehaviorEventRef";
    public const BPMN_ELEMENT_NONE_BEHAVIOR_EVENT_REF = "noneBehaviorEventRef";
    public const BPMN_ELEMENT_GROUP = "group";
    public const BPMN_ELEMENT_CATEGORY = "category";

    /** DC */

    public const DC_ELEMENT_FONT = "Font";
    public const DC_ELEMENT_POINT = "Point";
    public const DC_ELEMENT_BOUNDS = "Bounds";

    /** DI */

    public const DI_ELEMENT_DIAGRAM_ELEMENT = "DiagramElement";
    public const DI_ELEMENT_DIAGRAM = "Diagram";
    public const DI_ELEMENT_EDGE = "Edge";
    public const DI_ELEMENT_EXTENSION = "extension";
    public const DI_ELEMENT_LABELED_EDGE = "LabeledEdge";
    public const DI_ELEMENT_LABEL = "Label";
    public const DI_ELEMENT_LABELED_SHAPE = "LabeledShape";
    public const DI_ELEMENT_NODE = "Node";
    public const DI_ELEMENT_PLANE = "Plane";
    public const DI_ELEMENT_SHAPE = "Shape";
    public const DI_ELEMENT_STYLE = "Style";
    public const DI_ELEMENT_WAYPOINT = "waypoint";

    /** BPMNDI */

    public const BPMNDI_ELEMENT_BPMN_DIAGRAM = "BPMNDiagram";
    public const BPMNDI_ELEMENT_BPMN_PLANE = "BPMNPlane";
    public const BPMNDI_ELEMENT_BPMN_LABEL_STYLE = "BPMNLabelStyle";
    public const BPMNDI_ELEMENT_BPMN_SHAPE = "BPMNShape";
    public const BPMNDI_ELEMENT_BPMN_LABEL = "BPMNLabel";
    public const BPMNDI_ELEMENT_BPMN_EDGE = "BPMNEdge";

    /** extensions */

    public const EXTENSION_ELEMENT_CONNECTOR = "connector";
    public const EXTENSION_ELEMENT_CONNECTOR_ID = "connectorId";
    public const EXTENSION_ELEMENT_CONSTRAINT = "constraint";
    public const EXTENSION_ELEMENT_ENTRY = "entry";
    public const EXTENSION_ELEMENT_ERROR_EVENT_DEFINITION = "errorEventDefinition";
    public const EXTENSION_ELEMENT_EXECUTION_LISTENER = "executionListener";
    public const EXTENSION_ELEMENT_EXPRESSION = "expression";
    public const EXTENSION_ELEMENT_FAILED_JOB_RETRY_TIME_CYCLE = "failedJobRetryTimeCycle";
    public const EXTENSION_ELEMENT_FIELD = "field";
    public const EXTENSION_ELEMENT_FORM_DATA = "formData";
    public const EXTENSION_ELEMENT_FORM_FIELD = "formField";
    public const EXTENSION_ELEMENT_FORM_PROPERTY = "formProperty";
    public const EXTENSION_ELEMENT_IN = "in";
    public const EXTENSION_ELEMENT_INPUT_OUTPUT = "inputOutput";
    public const EXTENSION_ELEMENT_INPUT_PARAMETER = "inputParameter";
    public const EXTENSION_ELEMENT_LIST = "list";
    public const EXTENSION_ELEMENT_MAP = "map";
    public const EXTENSION_ELEMENT_OUTPUT_PARAMETER = "outputParameter";
    public const EXTENSION_ELEMENT_OUT = "out";
    public const EXTENSION_ELEMENT_POTENTIAL_STARTER = "potentialStarter";
    public const EXTENSION_ELEMENT_PROPERTIES = "properties";
    public const EXTENSION_ELEMENT_PROPERTY = "property";
    public const EXTENSION_ELEMENT_SCRIPT = "script";
    public const EXTENSION_ELEMENT_STRING = "string";
    public const EXTENSION_ELEMENT_TASK_LISTENER = "taskListener";
    public const EXTENSION_ELEMENT_VALIDATION = "validation";
    public const EXTENSION_ELEMENT_VALUE = "value";

    // attributes //////////////////////////////////////

    /** XSI attributes **/

    public const XSI_ATTRIBUTE_TYPE = "type";

    /** BPMN attributes **/

    public const BPMN_ATTRIBUTE_EXPORTER = "exporter";
    public const BPMN_ATTRIBUTE_EXPORTER_VERSION = "exporterVersion";
    public const BPMN_ATTRIBUTE_EXPRESSION_LANGUAGE = "expressionLanguage";
    public const BPMN_ATTRIBUTE_ID = "id";
    public const BPMN_ATTRIBUTE_NAME = "name";
    public const BPMN_ATTRIBUTE_TARGET_NAMESPACE = "targetNamespace";
    public const BPMN_ATTRIBUTE_TYPE_LANGUAGE = "typeLanguage";
    public const BPMN_ATTRIBUTE_NAMESPACE = "namespace";
    public const BPMN_ATTRIBUTE_LOCATION = "location";
    public const BPMN_ATTRIBUTE_IMPORT_TYPE = "importType";
    public const BPMN_ATTRIBUTE_TEXT_FORMAT = "textFormat";
    public const BPMN_ATTRIBUTE_PROCESS_TYPE = "processType";
    public const BPMN_ATTRIBUTE_IS_CLOSED = "isClosed";
    public const BPMN_ATTRIBUTE_IS_EXECUTABLE = "isExecutable";
    public const BPMN_ATTRIBUTE_MESSAGE_REF = "messageRef";
    public const BPMN_ATTRIBUTE_DEFINITION = "definition";
    public const BPMN_ATTRIBUTE_MUST_UNDERSTAND = "mustUnderstand";
    public const BPMN_ATTRIBUTE_TYPE = "type";
    public const BPMN_ATTRIBUTE_DIRECTION = "direction";
    public const BPMN_ATTRIBUTE_SOURCE_REF = "sourceRef";
    public const BPMN_ATTRIBUTE_TARGET_REF = "targetRef";
    public const BPMN_ATTRIBUTE_IS_IMMEDIATE = "isImmediate";
    public const BPMN_ATTRIBUTE_VALUE = "value";
    public const BPMN_ATTRIBUTE_STRUCTURE_REF = "structureRef";
    public const BPMN_ATTRIBUTE_IS_COLLECTION = "isCollection";
    public const BPMN_ATTRIBUTE_ITEM_KIND = "itemKind";
    public const BPMN_ATTRIBUTE_ITEM_REF = "itemRef";
    public const BPMN_ATTRIBUTE_ITEM_SUBJECT_REF = "itemSubjectRef";
    public const BPMN_ATTRIBUTE_ERROR_CODE = "errorCode";
    public const BPMN_ATTRIBUTE_LANGUAGE = "language";
    public const BPMN_ATTRIBUTE_EVALUATES_TO_TYPE_REF = "evaluatesToTypeRef";
    public const BPMN_ATTRIBUTE_PARALLEL_MULTIPLE = "parallelMultiple";
    public const BPMN_ATTRIBUTE_IS_INTERRUPTING = "isInterrupting";
    public const BPMN_ATTRIBUTE_IS_REQUIRED = "isRequired";
    public const BPMN_ATTRIBUTE_PARAMETER_REF = "parameterRef";
    public const BPMN_ATTRIBUTE_IS_FOR_COMPENSATION = "isForCompensation";
    public const BPMN_ATTRIBUTE_START_QUANTITY = "startQuantity";
    public const BPMN_ATTRIBUTE_COMPLETION_QUANTITY = "completionQuantity";
    public const BPMN_ATTRIBUTE_DEFAULT = "default";
    public const BPMN_ATTRIBUTE_OPERATION_REF = "operationRef";
    public const BPMN_ATTRIBUTE_INPUT_DATA_REF = "inputDataRef";
    public const BPMN_ATTRIBUTE_OUTPUT_DATA_REF = "outputDataRef";
    public const BPMN_ATTRIBUTE_IMPLEMENTATION_REF = "implementationRef";
    public const BPMN_ATTRIBUTE_PARTITION_ELEMENT_REF = "partitionElementRef";
    public const BPMN_ATTRIBUTE_CORRELATION_PROPERTY_REF = "correlationPropertyRef";
    public const BPMN_ATTRIBUTE_CORRELATION_KEY_REF = "correlationKeyRef";
    public const BPMN_ATTRIBUTE_IMPLEMENTATION = "implementation";
    public const BPMN_ATTRIBUTE_SCRIPT_FORMAT = "scriptFormat";
    public const BPMN_ATTRIBUTE_INSTANTIATE = "instantiate";
    public const BPMN_ATTRIBUTE_CANCEL_ACTIVITY = "cancelActivity";
    public const BPMN_ATTRIBUTE_ATTACHED_TO_REF = "attachedToRef";
    public const BPMN_ATTRIBUTE_TRIGGERED_BY_EVENT = "triggeredByEvent";
    public const BPMN_ATTRIBUTE_GATEWAY_DIRECTION = "gatewayDirection";
    public const BPMN_ATTRIBUTE_CALLED_ELEMENT = "calledElement";
    public const BPMN_ATTRIBUTE_MINIMUM = "minimum";
    public const BPMN_ATTRIBUTE_MAXIMUM = "maximum";
    public const BPMN_ATTRIBUTE_PROCESS_REF = "processRef";
    public const BPMN_ATTRIBUTE_CALLED_COLLABORATION_REF = "calledCollaborationRef";
    public const BPMN_ATTRIBUTE_INNER_CONVERSATION_NODE_REF = "innerConversationNodeRef";
    public const BPMN_ATTRIBUTE_OUTER_CONVERSATION_NODE_REF = "outerConversationNodeRef";
    public const BPMN_ATTRIBUTE_INNER_MESSAGE_FLOW_REF = "innerMessageFlowRef";
    public const BPMN_ATTRIBUTE_OUTER_MESSAGE_FLOW_REF = "outerMessageFlowRef";
    public const BPMN_ATTRIBUTE_ASSOCIATION_DIRECTION = "associationDirection";
    public const BPMN_ATTRIBUTE_WAIT_FOR_COMPLETION = "waitForCompletion";
    public const BPMN_ATTRIBUTE_ACTIVITY_REF = "activityRef";
    public const BPMN_ATTRIBUTE_ERROR_REF = "errorRef";
    public const BPMN_ATTRIBUTE_SIGNAL_REF = "signalRef";
    public const BPMN_ATTRIBUTE_ESCALATION_CODE = "escalationCode";
    public const BPMN_ATTRIBUTE_ESCALATION_REF = "escalationRef";
    public const BPMN_ATTRIBUTE_EVENT_GATEWAY_TYPE = "eventGatewayType";
    public const BPMN_ATTRIBUTE_DATA_OBJECT_REF = "dataObjectRef";
    public const BPMN_ATTRIBUTE_DATA_STORE_REF = "dataStoreRef";
    public const BPMN_ATTRIBUTE_METHOD = "method";
    public const BPMN_ATTRIBUTE_CAPACITY = "capacity";
    public const BPMN_ATTRIBUTE_IS_UNLIMITED = "isUnlimited";
    public const BPMN_ATTRIBUTE_CATEGORY_VALUE_REF = "categoryValueRef";

    /** DC */

    public const DC_ATTRIBUTE_NAME = "name";
    public const DC_ATTRIBUTE_SIZE = "size";
    public const DC_ATTRIBUTE_IS_BOLD = "isBold";
    public const DC_ATTRIBUTE_IS_ITALIC = "isItalic";
    public const DC_ATTRIBUTE_IS_UNDERLINE = "isUnderline";
    public const DC_ATTRIBUTE_IS_STRIKE_THROUGH = "isStrikeThrough";
    public const DC_ATTRIBUTE_X = "x";
    public const DC_ATTRIBUTE_Y = "y";
    public const DC_ATTRIBUTE_WIDTH = "width";
    public const DC_ATTRIBUTE_HEIGHT = "height";

    /** DI */

    public const DI_ATTRIBUTE_ID = "id";
    public const DI_ATTRIBUTE_NAME = "name";
    public const DI_ATTRIBUTE_DOCUMENTATION = "documentation";
    public const DI_ATTRIBUTE_RESOLUTION = "resolution";

    /** BPMNDI */

    public const BPMNDI_ATTRIBUTE_BPMN_ELEMENT = "bpmnElement";
    public const BPMNDI_ATTRIBUTE_SOURCE_ELEMENT = "sourceElement";
    public const BPMNDI_ATTRIBUTE_TARGET_ELEMENT = "targetElement";
    public const BPMNDI_ATTRIBUTE_MESSAGE_VISIBLE_KIND = "messageVisibleKind";
    public const BPMNDI_ATTRIBUTE_IS_HORIZONTAL = "isHorizontal";
    public const BPMNDI_ATTRIBUTE_IS_EXPANDED = "isExpanded";
    public const BPMNDI_ATTRIBUTE_IS_MARKER_VISIBLE = "isMarkerVisible";
    public const BPMNDI_ATTRIBUTE_IS_MESSAGE_VISIBLE = "isMessageVisible";
    public const BPMNDI_ATTRIBUTE_PARTICIPANT_BAND_KIND = "participantBandKind";
    public const BPMNDI_ATTRIBUTE_CHOREOGRAPHY_ACTIVITY_SHAPE = "choreographyActivityShape";
    public const BPMNDI_ATTRIBUTE_LABEL_STYLE = "labelStyle";

    /** extensions */

    public const EXTENSION_ATTRIBUTE_ASSIGNEE = "assignee";
    public const EXTENSION_ATTRIBUTE_ASYNC = "async";
    public const EXTENSION_ATTRIBUTE_ASYNC_BEFORE = "asyncBefore";
    public const EXTENSION_ATTRIBUTE_ASYNC_AFTER = "asyncAfter";
    public const EXTENSION_ATTRIBUTE_BUSINESS_KEY = "businessKey";
    public const EXTENSION_ATTRIBUTE_CALLED_ELEMENT_BINDING = "calledElementBinding";
    public const EXTENSION_ATTRIBUTE_CALLED_ELEMENT_VERSION = "calledElementVersion";
    public const EXTENSION_ATTRIBUTE_CALLED_ELEMENT_VERSION_TAG = "calledElementVersionTag";
    public const EXTENSION_ATTRIBUTE_CALLED_ELEMENT_TENANT_ID = "calledElementTenantId";
    public const EXTENSION_ATTRIBUTE_CANDIDATE_GROUPS = "candidateGroups";
    public const EXTENSION_ATTRIBUTE_CANDIDATE_STARTER_GROUPS = "candidateStarterGroups";
    public const EXTENSION_ATTRIBUTE_CANDIDATE_STARTER_USERS = "candidateStarterUsers";
    public const EXTENSION_ATTRIBUTE_CANDIDATE_USERS = "candidateUsers";
    public const EXTENSION_ATTRIBUTE_CLASS = "class";
    public const EXTENSION_ATTRIBUTE_COLLECTION = "collection";
    public const EXTENSION_ATTRIBUTE_CONFIG = "config";
    public const EXTENSION_ATTRIBUTE_DATE_PATTERN = "datePattern";
    public const EXTENSION_ATTRIBUTE_DECISION_REF = "decisionRef";
    public const EXTENSION_ATTRIBUTE_DECISION_REF_BINDING = "decisionRefBinding";
    public const EXTENSION_ATTRIBUTE_DECISION_REF_VERSION = "decisionRefVersion";
    public const EXTENSION_ATTRIBUTE_DECISION_REF_VERSION_TAG = "decisionRefVersionTag";
    public const EXTENSION_ATTRIBUTE_DECISION_REF_TENANT_ID = "decisionRefTenantId";
    public const EXTENSION_ATTRIBUTE_DEFAULT = "default";
    public const EXTENSION_ATTRIBUTE_DEFAULT_VALUE = "defaultValue";
    public const EXTENSION_ATTRIBUTE_DELEGATE_EXPRESSION = "delegateExpression";
    public const EXTENSION_ATTRIBUTE_DUE_DATE = "dueDate";
    public const EXTENSION_ATTRIBUTE_FOLLOW_UP_DATE = "followUpDate";
    public const EXTENSION_ATTRIBUTE_ELEMENT_VARIABLE = "elementVariable";
    public const EXTENSION_ATTRIBUTE_EVENT = "event";
    public const EXTENSION_ATTRIBUTE_ERROR_CODE_VARIABLE = "errorCodeVariable";
    public const EXTENSION_ATTRIBUTE_ERROR_MESSAGE_VARIABLE = "errorMessageVariable";
    public const EXTENSION_ATTRIBUTE_ERROR_MESSAGE = "errorMessage";
    public const EXTENSION_ATTRIBUTE_EXCLUSIVE = "exclusive";
    public const EXTENSION_ATTRIBUTE_EXPRESSION = "expression";
    public const EXTENSION_ATTRIBUTE_FORM_HANDLER_CLASS = "formHandlerClass";
    public const EXTENSION_ATTRIBUTE_FORM_KEY = "formKey";
    public const EXTENSION_ATTRIBUTE_ID = "id";
    public const EXTENSION_ATTRIBUTE_INITIATOR = "initiator";
    public const EXTENSION_ATTRIBUTE_JOB_PRIORITY = "jobPriority";
    public const EXTENSION_ATTRIBUTE_TASK_PRIORITY = "taskPriority";
    public const EXTENSION_ATTRIBUTE_KEY = "key";
    public const EXTENSION_ATTRIBUTE_LABEL = "label";
    public const EXTENSION_ATTRIBUTE_LOCAL = "local";
    public const EXTENSION_ATTRIBUTE_MAP_DECISION_RESULT = "mapDecisionResult";
    public const EXTENSION_ATTRIBUTE_NAME = "name";
    public const EXTENSION_ATTRIBUTE_PRIORITY = "priority";
    public const EXTENSION_ATTRIBUTE_READABLE = "readable";
    public const EXTENSION_ATTRIBUTE_REQUIRED = "required";
    public const EXTENSION_ATTRIBUTE_RESOURCE = "resource";
    public const EXTENSION_ATTRIBUTE_RESULT_VARIABLE = "resultVariable";
    public const EXTENSION_ATTRIBUTE_SCRIPT_FORMAT = "scriptFormat";
    public const EXTENSION_ATTRIBUTE_SOURCE = "source";
    public const EXTENSION_ATTRIBUTE_SOURCE_EXPRESSION = "sourceExpression";
    public const EXTENSION_ATTRIBUTE_STRING_VALUE = "stringValue";
    public const EXTENSION_ATTRIBUTE_TARGET = "target";
    public const EXTENSION_ATTRIBUTE_TOPIC = "topic";
    public const EXTENSION_ATTRIBUTE_TYPE = "type";
    public const EXTENSION_ATTRIBUTE_VALUE = "value";
    public const EXTENSION_ATTRIBUTE_VARIABLE = "variable";
    public const EXTENSION_ATTRIBUTE_VARIABLE_MAPPING_CLASS = "variableMappingClass";
    public const EXTENSION_ATTRIBUTE_VARIABLE_MAPPING_DELEGATE_EXPRESSION = "variableMappingDelegateExpression";
    public const EXTENSION_ATTRIBUTE_VARIABLES = "variables";
    public const EXTENSION_ATTRIBUTE_WRITEABLE = "writeable";
    public const EXTENSION_ATTRIBUTE_CASE_REF = "caseRef";
    public const EXTENSION_ATTRIBUTE_CASE_BINDING = "caseBinding";
    public const EXTENSION_ATTRIBUTE_CASE_VERSION = "caseVersion";
    public const EXTENSION_ATTRIBUTE_CASE_TENANT_ID = "caseTenantId";
    public const EXTENSION_ATTRIBUTE_VARIABLE_NAME = "variableName";
    public const EXTENSION_ATTRIBUTE_VARIABLE_EVENTS = "variableEvents";
    public const EXTENSION_ATTRIBUTE_HISTORY_TIME_TO_LIVE = "historyTimeToLive";
    public const EXTENSION_ATTRIBUTE_IS_STARTABLE_IN_TASKLIST = "isStartableInTasklist";
    public const EXTENSION_ATTRIBUTE_VERSION_TAG = "versionTag";
}
