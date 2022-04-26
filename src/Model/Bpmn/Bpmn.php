<?php

namespace Jabe\Model\Bpmn;

use Jabe\Model\Xml\Impl\Instance\ModelElementInstanceImpl;
use Jabe\Model\Bpmn\Builder\ProcessBuilder;
use Jabe\Model\Bpmn\Exception\BpmnModelException;
use Jabe\Model\Bpmn\Impl\{
    BpmnModelConstants,
    BpmnParser
};
use Jabe\Model\Bpmn\Instance\{
    DefinitionsInterface,
    ProcessInterface
};
use Jabe\Model\Bpmn\Instance\Bpmndi\{
    BpmnDiagramInterface,
    BpmnPlaneInterface
};
use Jabe\Model\Bpmn\Impl\Instance\{
    ActivationConditionImpl,
    ActivityImpl,
    ArtifactImpl,
    AssignmentImpl,
    AssociationImpl,
    AuditingImpl,
    BaseElementImpl,
    BoundaryEventImpl,
    BpmnModelElementInstanceImpl,
    BusinessRuleTaskImpl,
    CallableElementImpl,
    CallActivityImpl,
    CallConversationImpl,
    CancelEventDefinitionImpl,
    CatchEventImpl,
    CategoryImpl,
    CategoryValueImpl,
    CategoryValueRef,
    ChildLaneSet,
    CollaborationImpl,
    CompensateEventDefinitionImpl,
    ConditionImpl,
    ConditionalEventDefinitionImpl,
    CompletionConditionImpl,
    ComplexBehaviorDefinitionImpl,
    ComplexGatewayImpl,
    ConditionExpressionImpl,
    ConversationAssociationImpl,
    ConversationImpl,
    ConversationLinkImpl,
    ConversationNodeImpl,
    CorrelationKeyImpl,
    CorrelationPropertyBindingImpl,
    CorrelationPropertyImpl,
    CorrelationPropertyRef,
    CorrelationPropertyRetrievalExpressionImpl,
    CorrelationSubscriptionImpl,
    DataAssociationImpl,
    DataInputAssociationImpl,
    DataInputImpl,
    DataInputRefs,
    DataObjectImpl,
    DataObjectReferenceImpl,
    DataOutputAssociationImpl,
    DataOutputImpl,
    DataOutputRefs,
    DataPath,
    DataStateImpl,
    DataStoreImpl,
    DataStoreReferenceImpl,
    DefinitionsImpl,
    DocumentationImpl,
    EndEventImpl,
    EndPointImpl,
    EndPointRef,
    ErrorEventDefinitionImpl,
    ErrorImpl,
    ErrorRef,
    EscalationEventDefinitionImpl,
    EscalationImpl,
    EventBasedGatewayImpl,
    EventDefinitionImpl,
    EventDefinitionRef,
    EventImpl,
    ExclusiveGatewayImpl,
    ExpressionImpl,
    ExtensionElementsImpl,
    ExtensionImpl,
    FlowElementImpl,
    FlowNodeImpl,
    FlowNodeRef,
    FormalExpressionImpl,
    From,
    GatewayImpl,
    GlobalConversationImpl,
    GroupImpl,
    HumanPerformerImpl,
    ImportImpl,
    InclusiveGatewayImpl,
    Incoming,
    InMessageRef,
    InnerParticipantRef,
    InputDataItemImpl,
    InputSetImpl,
    InputSetRefs,
    InteractionNodeImpl,
    InterfaceImpl,
    InterfaceRef,
    IntermediateCatchEventImpl,
    IntermediateThrowEventImpl,
    IoBindingImpl,
    IoSpecificationImpl,
    ItemAwareElementImpl,
    ItemDefinitionImpl,
    LaneImpl,
    LaneSetImpl,
    LinkEventDefinitionImpl,
    LoopCardinalityImpl,
    LoopCharacteristicsImpl,
    LoopDataInputRef,
    LoopDataOutputRef,
    ManualTaskImpl,
    MessageEventDefinitionImpl,
    MessageFlowAssociationImpl,
    MessageFlowImpl,
    MessageFlowRef,
    MessageImpl,
    MessagePath,
    MonitoringImpl,
    MultiInstanceLoopCharacteristicsImpl,
    OperationImpl,
    OperationRef,
    OptionalInputRefs,
    OptionalOutputRefs,
    OuterParticipantRef,
    Outgoing,
    OutMessageRef,
    OutputDataItemImpl,
    OutputSetImpl,
    OutputSetRefs,
    ParallelGatewayImpl,
    ParticipantAssociationImpl,
    ParticipantImpl,
    ParticipantMultiplicityImpl,
    ParticipantRef,
    PartitionElement,
    PerformerImpl,
    PotentialOwnerImpl,
    ProcessImpl,
    PropertyImpl,
    ReceiveTaskImpl,
    RelationshipImpl,
    RenderingImpl,
    ResourceAssignmentExpressionImpl,
    ResourceImpl,
    ResourceParameterBindingImpl,
    ResourceParameterImpl,
    ResourceRef,
    ResourceRoleImpl,
    RootElementImpl,
    ScriptImpl,
    ScriptTaskImpl,
    SendTaskImpl,
    SequenceFlowImpl,
    ServiceTaskImpl,
    SignalEventDefinitionImpl,
    SignalImpl,
    Source,
    SourceRef,
    StartEventImpl,
    SubConversationImpl,
    SubProcessImpl,
    SupportedInterfaceRef,
    Supports,
    Target,
    TargetRef,
    TaskImpl,
    TerminateEventDefinitionImpl,
    TextAnnotationImpl,
    TextImpl,
    ThrowEventImpl,
    TimeCycleImpl,
    TimeDateImpl,
    TimeDurationImpl,
    TimerEventDefinitionImpl,
    To,
    TransactionImpl,
    Transformation,
    UserTaskImpl,
    WhileExecutingInputRefs,
    WhileExecutingOutputRefs
};
use Jabe\Model\Bpmn\Impl\Instance\Bpmndi\{
    BpmnDiagramImpl,
    BpmnEdgeImpl,
    BpmnLabelImpl,
    BpmnLabelStyleImpl,
    BpmnPlaneImpl,
    BpmnShapeImpl
};
use Jabe\Model\Bpmn\Impl\Instance\Dc\{
    BoundsImpl,
    FontImpl,
    PointImpl
};
use Jabe\Model\Bpmn\Impl\Instance\Di\{
    DiagramElementImpl,
    DiagramImpl,
    EdgeImpl,
    ExtensionImpl as DiExtensionImpl,
    LabeledEdgeImpl,
    LabeledShapeImpl,
    LabelImpl,
    NodeImpl,
    PlaneImpl,
    ShapeImpl,
    StyleImpl,
    WaypointImpl
};
use Jabe\Model\Bpmn\Impl\Instance\Extension\{
    ConnectorIdImpl,
    ConnectorImpl,
    ConstraintImpl,
    EntryImpl,
    ErrorEventDefinitionImpl as ExtErrorEventDefinitionImpl,
    ExecutionListenerImpl,
    ExpressionImpl as ExtExpressionImpl,
    FailedJobRetryTimeCycleImpl,
    FieldImpl,
    FormDataImpl,
    FormFieldImpl,
    FormPropertyImpl,
    GenericValueElementImpl,
    InImpl,
    InputOutputImpl,
    InputParameterImpl,
    ListImpl,
    MapImpl,
    OutImpl,
    OutputParameterImpl,
    PotentialStarterImpl,
    PropertiesImpl,
    PropertyImpl as ExtPropertyImpl,
    ScriptImpl as ExtScriptImpl,
    StringImpl,
    TaskListenerImpl,
    ValidationImpl,
    ValueImpl
};
use Jabe\Model\Xml\{
    ModelBuilder,
    ModelInterface
};
use Jabe\Model\Xml\Impl\Util\IoUtil;

class Bpmn
{
    private static $INSTANCE;

    private $bpmnParser;

    private $bpmnModelBuilder;

    private $bpmnModel;

    public static function getInstance(): Bpmn
    {
        if (self::$INSTANCE == null) {
            self::$INSTANCE = new Bpmn();
            self::$INSTANCE->bpmnParser = new BpmnParser();
        }
        return self::$INSTANCE;
    }

    protected function __construct()
    {
        $this->bpmnModelBuilder = ModelBuilder::createInstance("BPMN Model");
        $this->bpmnModelBuilder->alternativeNamespace(
            BpmnModelConstants::ACTIVITI_NS,
            BpmnModelConstants::EXTENSION_NS
        );
        $this->doRegisterTypes($this->bpmnModelBuilder);
        $this->bpmnModel = $this->bpmnModelBuilder->build();
    }

    public static function readModelFromFile(string $filePath): BpmnModelInstanceInterface
    {
        return self::$INSTANCE->doReadModelFromFile($filePath);
    }

    protected function doReadModelFromFile(string $filePath): BpmnModelInstanceInterface
    {
        try {
            $fd = fopen($filePath, "r");
            return $this->doReadModelFromInputStream($fd);
        } catch (\Exception $e) {
            throw new BpmnModelException(sprintf("Cannot read model from file %s: file does not exist.", $filePath));
        } finally {
            fclose($fd);
        }
    }

    /**
     * @param resource $stream
     */
    protected function doReadModelFromInputStream($stream): BpmnModelInstanceInterface
    {
        return $this->bpmnParser->parseModelFromStream($stream);
    }

    /**
     * @param resource $stream
     */
    public static function readModelFromStream($stream): BpmnModelInstanceInterface
    {
        return self::$INSTANCE->doReadModelFromInputStream($stream);
    }

    public static function writeModelToFile(string $filePath, BpmnModelInstanceInterface $modelInstance): void
    {
        self::$INSTANCE->doWriteModelToFile($filePath, $modelInstance);
    }

    protected function doWriteModelToFile(
        string $filePath,
        BpmnModelInstanceInterface $modelInstance
    ): void {
        try {
            $fd = fopen($filePath, "r+");
            $this->doWriteModelToOutputStream($fd, $modelInstance);
            fclose($fd);
        } catch (\Exception $e) {
            throw new BpmnModelException(sprintf("Cannot write model to file %s: file does not exist.", $filePath));
        } finally {
            fclose($fd);
        }
    }

    /**
     * @param resource $stream
     * @param BpmnModelInstanceInterface $modelInstance
     */
    public static function writeModelToStream($stream, BpmnModelInstanceInterface $modelInstance): void
    {
        self::$INSTANCE->doWriteModelToOutputStream($stream, $modelInstance);
    }

    /**
     * @param resource $stream
     * @param BpmnModelInstanceInterface $modelInstance
     */
    protected function doWriteModelToOutputStream($stream, BpmnModelInstanceInterface $modelInstance): void
    {
        try {
            $this->doValidateModel($modelInstance);
            IoUtil::writeDocumentToOutputStream($modelInstance->getDocument(), $stream);
            fclose($stream);
        } catch (\Exception $e) {
            throw new BpmnModelException(sprintf("Cannot write model to file, file does not exist."));
        }
    }

    public static function convertToString(BpmnModelInstanceInterface $modelInstance): string
    {
        return self::$INSTANCE->doConvertToString($modelInstance);
    }

    protected function doConvertToString(BpmnModelInstanceInterface $modelInstance): string
    {
        $this->doValidateModel($modelInstance);
        return IoUtil::convertXmlDocumentToString($modelInstance->getDocument());
    }

    protected function doValidateModel(BpmnModelInstanceInterface $modelInstance): void
    {
        $this->bpmnParser->validateModel($modelInstance->getDocument());
    }

    public static function validateModel(BpmnModelInstanceInterface $modelInstance): void
    {
        self::$INSTANCE->doValidateModel($modelInstance);
    }

    public static function createEmptyModel(): BpmnModelInstanceInterface
    {
        return self::$INSTANCE->doCreateEmptyModel();
    }

    protected function doCreateEmptyModel(): BpmnModelInstanceInterface
    {
        return $this->bpmnParser->getEmptyModel();
    }

    public static function createProcess(?string $processId = null): ProcessBuilder
    {
        $modelInstance = self::$INSTANCE->doCreateEmptyModel();
        $definitions = $modelInstance->newInstance(DefinitionsInterface::class);
        $definitions->setTargetNamespace(BpmnModelConstants::BPMN20_NS);
        $definitions->getDomElement()->registerNamespace("extension", BpmnModelConstants::EXTENSION_NS);
        $modelInstance->setDefinitions($definitions);
        $process = $modelInstance->newInstance(ProcessInterface::class);
        $definitions->addChildElement($process);

        $bpmnDiagram = $modelInstance->newInstance(BpmnDiagramInterface::class);

        $bpmnPlane = $modelInstance->newInstance(BpmnPlaneInterface::class);
        $bpmnPlane->setBpmnElement($process);

        $bpmnDiagram->addChildElement($bpmnPlane);
        $definitions->addChildElement($bpmnDiagram);

        $builder = $process->builder();

        if ($processId != null) {
            $builder->id($processId);
        }

        return $builder;
    }

    public static function createExecutableProcess(?string $processId = null): ProcessBuilder
    {
        return self::createProcess($processId)->executable();
    }

    protected function doRegisterTypes(ModelBuilder $bpmnModelBuilder): void
    {
        ActivationConditionImpl::registerType($bpmnModelBuilder);
        ActivityImpl::registerType($bpmnModelBuilder);
        ArtifactImpl::registerType($bpmnModelBuilder);
        AssignmentImpl::registerType($bpmnModelBuilder);
        AssociationImpl::registerType($bpmnModelBuilder);
        AuditingImpl::registerType($bpmnModelBuilder);
        BaseElementImpl::registerType($bpmnModelBuilder);
        BoundaryEventImpl::registerType($bpmnModelBuilder);
        BusinessRuleTaskImpl::registerType($bpmnModelBuilder);
        CallableElementImpl::registerType($bpmnModelBuilder);
        CallActivityImpl::registerType($bpmnModelBuilder);
        CallConversationImpl::registerType($bpmnModelBuilder);
        CancelEventDefinitionImpl::registerType($bpmnModelBuilder);
        CatchEventImpl::registerType($bpmnModelBuilder);
        CategoryImpl::registerType($bpmnModelBuilder);
        CategoryValueImpl::registerType($bpmnModelBuilder);
        CategoryValueRef::registerType($bpmnModelBuilder);
        ChildLaneSet::registerType($bpmnModelBuilder);
        CollaborationImpl::registerType($bpmnModelBuilder);
        CompensateEventDefinitionImpl::registerType($bpmnModelBuilder);
        ConditionImpl::registerType($bpmnModelBuilder);
        ConditionalEventDefinitionImpl::registerType($bpmnModelBuilder);
        CompletionConditionImpl::registerType($bpmnModelBuilder);
        ComplexBehaviorDefinitionImpl::registerType($bpmnModelBuilder);
        ComplexGatewayImpl::registerType($bpmnModelBuilder);
        ConditionExpressionImpl::registerType($bpmnModelBuilder);
        ConversationAssociationImpl::registerType($bpmnModelBuilder);
        ConversationImpl::registerType($bpmnModelBuilder);
        ConversationLinkImpl::registerType($bpmnModelBuilder);
        ConversationNodeImpl::registerType($bpmnModelBuilder);
        CorrelationKeyImpl::registerType($bpmnModelBuilder);
        CorrelationPropertyBindingImpl::registerType($bpmnModelBuilder);
        CorrelationPropertyImpl::registerType($bpmnModelBuilder);
        CorrelationPropertyRef::registerType($bpmnModelBuilder);
        CorrelationPropertyRetrievalExpressionImpl::registerType($bpmnModelBuilder);
        CorrelationSubscriptionImpl::registerType($bpmnModelBuilder);
        DataAssociationImpl::registerType($bpmnModelBuilder);
        DataInputAssociationImpl::registerType($bpmnModelBuilder);
        DataInputImpl::registerType($bpmnModelBuilder);
        DataInputRefs::registerType($bpmnModelBuilder);
        DataOutputAssociationImpl::registerType($bpmnModelBuilder);
        DataOutputImpl::registerType($bpmnModelBuilder);
        DataOutputRefs::registerType($bpmnModelBuilder);
        DataPath::registerType($bpmnModelBuilder);
        DataStateImpl::registerType($bpmnModelBuilder);
        DataObjectImpl::registerType($bpmnModelBuilder);
        DataObjectReferenceImpl::registerType($bpmnModelBuilder);
        DataStoreImpl::registerType($bpmnModelBuilder);
        DataStoreReferenceImpl::registerType($bpmnModelBuilder);
        DefinitionsImpl::registerType($bpmnModelBuilder);
        DocumentationImpl::registerType($bpmnModelBuilder);
        EndEventImpl::registerType($bpmnModelBuilder);
        EndPointImpl::registerType($bpmnModelBuilder);
        EndPointRef::registerType($bpmnModelBuilder);
        ErrorEventDefinitionImpl::registerType($bpmnModelBuilder);
        ErrorImpl::registerType($bpmnModelBuilder);
        ErrorRef::registerType($bpmnModelBuilder);
        EscalationImpl::registerType($bpmnModelBuilder);
        EscalationEventDefinitionImpl::registerType($bpmnModelBuilder);
        EventBasedGatewayImpl::registerType($bpmnModelBuilder);
        EventDefinitionImpl::registerType($bpmnModelBuilder);
        EventDefinitionRef::registerType($bpmnModelBuilder);
        EventImpl::registerType($bpmnModelBuilder);
        ExclusiveGatewayImpl::registerType($bpmnModelBuilder);
        ExpressionImpl::registerType($bpmnModelBuilder);
        ExtensionElementsImpl::registerType($bpmnModelBuilder);
        ExtensionImpl::registerType($bpmnModelBuilder);
        FlowElementImpl::registerType($bpmnModelBuilder);
        FlowNodeImpl::registerType($bpmnModelBuilder);
        FlowNodeRef::registerType($bpmnModelBuilder);
        FormalExpressionImpl::registerType($bpmnModelBuilder);
        From::registerType($bpmnModelBuilder);
        GatewayImpl::registerType($bpmnModelBuilder);
        GlobalConversationImpl::registerType($bpmnModelBuilder);
        GroupImpl::registerType($bpmnModelBuilder);
        HumanPerformerImpl::registerType($bpmnModelBuilder);
        ImportImpl::registerType($bpmnModelBuilder);
        InclusiveGatewayImpl::registerType($bpmnModelBuilder);
        Incoming::registerType($bpmnModelBuilder);
        InMessageRef::registerType($bpmnModelBuilder);
        InnerParticipantRef::registerType($bpmnModelBuilder);
        InputDataItemImpl::registerType($bpmnModelBuilder);
        InputSetImpl::registerType($bpmnModelBuilder);
        InputSetRefs::registerType($bpmnModelBuilder);
        InteractionNodeImpl::registerType($bpmnModelBuilder);
        InterfaceImpl::registerType($bpmnModelBuilder);
        InterfaceRef::registerType($bpmnModelBuilder);
        IntermediateCatchEventImpl::registerType($bpmnModelBuilder);
        IntermediateThrowEventImpl::registerType($bpmnModelBuilder);
        IoBindingImpl::registerType($bpmnModelBuilder);
        IoSpecificationImpl::registerType($bpmnModelBuilder);
        ItemAwareElementImpl::registerType($bpmnModelBuilder);
        ItemDefinitionImpl::registerType($bpmnModelBuilder);
        LaneImpl::registerType($bpmnModelBuilder);
        LaneSetImpl::registerType($bpmnModelBuilder);
        LinkEventDefinitionImpl::registerType($bpmnModelBuilder);
        LoopCardinalityImpl::registerType($bpmnModelBuilder);
        LoopCharacteristicsImpl::registerType($bpmnModelBuilder);
        LoopDataInputRef::registerType($bpmnModelBuilder);
        LoopDataOutputRef::registerType($bpmnModelBuilder);
        ManualTaskImpl::registerType($bpmnModelBuilder);
        MessageEventDefinitionImpl::registerType($bpmnModelBuilder);
        MessageFlowAssociationImpl::registerType($bpmnModelBuilder);
        MessageFlowImpl::registerType($bpmnModelBuilder);
        MessageFlowRef::registerType($bpmnModelBuilder);
        MessageImpl::registerType($bpmnModelBuilder);
        MessagePath::registerType($bpmnModelBuilder);
        ModelElementInstanceImpl::registerType($bpmnModelBuilder);
        MonitoringImpl::registerType($bpmnModelBuilder);
        MultiInstanceLoopCharacteristicsImpl::registerType($bpmnModelBuilder);
        OperationImpl::registerType($bpmnModelBuilder);
        OperationRef::registerType($bpmnModelBuilder);
        OptionalInputRefs::registerType($bpmnModelBuilder);
        OptionalOutputRefs::registerType($bpmnModelBuilder);
        OuterParticipantRef::registerType($bpmnModelBuilder);
        OutMessageRef::registerType($bpmnModelBuilder);
        Outgoing::registerType($bpmnModelBuilder);
        OutputDataItemImpl::registerType($bpmnModelBuilder);
        OutputSetImpl::registerType($bpmnModelBuilder);
        OutputSetRefs::registerType($bpmnModelBuilder);
        ParallelGatewayImpl::registerType($bpmnModelBuilder);
        ParticipantAssociationImpl::registerType($bpmnModelBuilder);
        ParticipantImpl::registerType($bpmnModelBuilder);
        ParticipantMultiplicityImpl::registerType($bpmnModelBuilder);
        ParticipantRef::registerType($bpmnModelBuilder);
        PartitionElement::registerType($bpmnModelBuilder);
        PerformerImpl::registerType($bpmnModelBuilder);
        PotentialOwnerImpl::registerType($bpmnModelBuilder);
        ProcessImpl::registerType($bpmnModelBuilder);
        PropertyImpl::registerType($bpmnModelBuilder);
        ReceiveTaskImpl::registerType($bpmnModelBuilder);
        RelationshipImpl::registerType($bpmnModelBuilder);
        RenderingImpl::registerType($bpmnModelBuilder);
        ResourceAssignmentExpressionImpl::registerType($bpmnModelBuilder);
        ResourceImpl::registerType($bpmnModelBuilder);
        ResourceParameterBindingImpl::registerType($bpmnModelBuilder);
        ResourceParameterImpl::registerType($bpmnModelBuilder);
        ResourceRef::registerType($bpmnModelBuilder);
        ResourceRoleImpl::registerType($bpmnModelBuilder);
        RootElementImpl::registerType($bpmnModelBuilder);
        ScriptImpl::registerType($bpmnModelBuilder);
        ScriptTaskImpl::registerType($bpmnModelBuilder);
        SendTaskImpl::registerType($bpmnModelBuilder);
        SequenceFlowImpl::registerType($bpmnModelBuilder);
        ServiceTaskImpl::registerType($bpmnModelBuilder);
        SignalEventDefinitionImpl::registerType($bpmnModelBuilder);
        SignalImpl::registerType($bpmnModelBuilder);
        Source::registerType($bpmnModelBuilder);
        SourceRef::registerType($bpmnModelBuilder);
        StartEventImpl::registerType($bpmnModelBuilder);
        SubConversationImpl::registerType($bpmnModelBuilder);
        SubProcessImpl::registerType($bpmnModelBuilder);
        SupportedInterfaceRef::registerType($bpmnModelBuilder);
        Supports::registerType($bpmnModelBuilder);
        Target::registerType($bpmnModelBuilder);
        TargetRef::registerType($bpmnModelBuilder);
        TaskImpl::registerType($bpmnModelBuilder);
        TerminateEventDefinitionImpl::registerType($bpmnModelBuilder);
        TextImpl::registerType($bpmnModelBuilder);
        TextAnnotationImpl::registerType($bpmnModelBuilder);
        ThrowEventImpl::registerType($bpmnModelBuilder);
        TimeCycleImpl::registerType($bpmnModelBuilder);
        TimeDateImpl::registerType($bpmnModelBuilder);
        TimeDurationImpl::registerType($bpmnModelBuilder);
        TimerEventDefinitionImpl::registerType($bpmnModelBuilder);
        To::registerType($bpmnModelBuilder);
        TransactionImpl::registerType($bpmnModelBuilder);
        Transformation::registerType($bpmnModelBuilder);
        UserTaskImpl::registerType($bpmnModelBuilder);
        WhileExecutingInputRefs::registerType($bpmnModelBuilder);
        WhileExecutingOutputRefs::registerType($bpmnModelBuilder);

        FontImpl::registerType($bpmnModelBuilder);
        PointImpl::registerType($bpmnModelBuilder);
        BoundsImpl::registerType($bpmnModelBuilder);

        DiagramImpl::registerType($bpmnModelBuilder);
        DiagramElementImpl::registerType($bpmnModelBuilder);
        EdgeImpl::registerType($bpmnModelBuilder);
        DiExtensionImpl::registerType($bpmnModelBuilder);
        LabelImpl::registerType($bpmnModelBuilder);
        LabeledEdgeImpl::registerType($bpmnModelBuilder);
        LabeledShapeImpl::registerType($bpmnModelBuilder);
        NodeImpl::registerType($bpmnModelBuilder);
        PlaneImpl::registerType($bpmnModelBuilder);
        ShapeImpl::registerType($bpmnModelBuilder);
        StyleImpl::registerType($bpmnModelBuilder);
        WaypointImpl::registerType($bpmnModelBuilder);

        BpmnDiagramImpl::registerType($bpmnModelBuilder);
        BpmnEdgeImpl::registerType($bpmnModelBuilder);
        BpmnLabelImpl::registerType($bpmnModelBuilder);
        BpmnLabelStyleImpl::registerType($bpmnModelBuilder);
        BpmnPlaneImpl::registerType($bpmnModelBuilder);
        BpmnShapeImpl::registerType($bpmnModelBuilder);

        ConnectorImpl::registerType($bpmnModelBuilder);
        ConnectorIdImpl::registerType($bpmnModelBuilder);
        ConstraintImpl::registerType($bpmnModelBuilder);
        EntryImpl::registerType($bpmnModelBuilder);
        ExtErrorEventDefinitionImpl::registerType($bpmnModelBuilder);
        ExecutionListenerImpl::registerType($bpmnModelBuilder);
        ExtExpressionImpl::registerType($bpmnModelBuilder);
        FailedJobRetryTimeCycleImpl::registerType($bpmnModelBuilder);
        FieldImpl::registerType($bpmnModelBuilder);
        FormDataImpl::registerType($bpmnModelBuilder);
        FormFieldImpl::registerType($bpmnModelBuilder);
        FormPropertyImpl::registerType($bpmnModelBuilder);
        InImpl::registerType($bpmnModelBuilder);
        InputOutputImpl::registerType($bpmnModelBuilder);
        InputParameterImpl::registerType($bpmnModelBuilder);
        ListImpl::registerType($bpmnModelBuilder);
        MapImpl::registerType($bpmnModelBuilder);
        OutputParameterImpl::registerType($bpmnModelBuilder);
        OutImpl::registerType($bpmnModelBuilder);
        PotentialStarterImpl::registerType($bpmnModelBuilder);
        PropertiesImpl::registerType($bpmnModelBuilder);
        ExtPropertyImpl::registerType($bpmnModelBuilder);
        ExtScriptImpl::registerType($bpmnModelBuilder);
        StringImpl::registerType($bpmnModelBuilder);
        TaskListenerImpl::registerType($bpmnModelBuilder);
        ValidationImpl::registerType($bpmnModelBuilder);
        ValueImpl::registerType($bpmnModelBuilder);
    }

    public function getBpmnModel(): ModelInterface
    {
        return $this->bpmnModel;
    }

    public function getBpmnModelBuilder(): ModelBuilder
    {
        return $this->bpmnModelBuilder;
    }

    /**
     * @param bpmnModel the bpmnModel to set
     */
    public function setBpmnModel(ModelInterface $bpmnModel): void
    {
        $this->bpmnModel = $bpmnModel;
    }
}
