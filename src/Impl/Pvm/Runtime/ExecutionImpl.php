<?php

namespace Jabe\Impl\Pvm\Runtime;

use Jabe\{
    ProcessEngineInterface,
    ProcessEngineServicesInterface
};
use Jabe\Delegate\{
    BpmnModelExecutionContextInterface,
    DelegateExecutionInterface,
    ProcessEngineServicesAwareInterface
};
use Jabe\Impl\Core\Variable\Scope\{
    SimpleVariableInstanceFactory,
    VariableInstanceFactoryInterface,
    VariableInstanceLifecycleListenerInterface,
    VariableStore
};
use Jabe\Impl\Pvm\PvmProcessInstanceInterface;
use Jabe\Impl\Pvm\Delegate\ActivityExecutionInterface;
use Bpmn\BpmnModelInstanceInterface;
use Bpmn\Instance\FlowElementInterface;

class ExecutionImpl extends PvmExecutionImpl implements ActivityExecutionInterface, PvmProcessInstanceInterface
{
    private static $idGenerator = 0;

    // current position /////////////////////////////////////////////////////////

    /** the process instance.  this is the root of the execution tree.
     * the processInstance of a process instance is a self reference. */
    protected $processInstance;

    /** the parent execution */
    protected $parent;

    /** nested executions representing scopes or concurrent paths */
    protected $executions = [];

    /** super execution, not-null if this execution is part of a subprocess */
    protected $superExecution;

    /** reference to a subprocessinstance, not-null if currently subprocess is started from this execution */
    protected $subProcessInstance;

    /** super case execution, not-null if this execution is part of a case execution */
    //protected CaseExecutionImpl superCaseExecution;

    /** reference to a subcaseinstance, not-null if currently subcase is started from this execution */
    //protected CaseExecutionImpl subCaseInstance;

    // variables/////////////////////////////////////////////////////////////////

    protected $variableStore;

    // lifecycle methods ////////////////////////////////////////////////////////

    public function __construct()
    {
        parent::__construct();
        $this->variableStore = new VariableStore();
    }

    /** creates a new execution. properties processDefinition, processInstance and activity will be initialized. */
    public function createExecution(): ExecutionImpl
    {
        // create the new child execution
        $createdExecution = $this->newExecution();
        // initialize sequence counter
        $createdExecution->setSequenceCounter($this->getSequenceCounter());

        // manage the bidirectional parent-child relation
        $createdExecution->setParent($this);

        // initialize the new execution
        $createdExecution->setProcessDefinition($this->getProcessDefinition());
        $createdExecution->setProcessInstance($this->getProcessInstance());
        $createdExecution->setActivity($this->getActivity());

        // make created execution start in same activity instance
        $createdExecution->activityInstanceId = $this->activityInstanceId;

        // with the fix of CAM-9249 we presume that the parent and the child have the same startContext
        $createdExecution->setStartContext($this->scopeInstantiationContext);

        $createdExecution->skipCustomListeners = $this->skipCustomListeners;
        $createdExecution->skipIoMapping = $this->skipIoMapping;

        return $createdExecution;
    }

    /** instantiates a new execution.  can be overridden by subclasses */
    protected function newExecution(): ExecutionImpl
    {
        return new ExecutionImpl();
    }

    public function initialize(): void
    {
    }

    public function initializeTimerDeclarations(): void
    {
    }

    // parent ///////////////////////////////////////////////////////////////////

    /** ensures initialization and returns the parent */
    public function getParent(): ExecutionImpl
    {
        return $this->parent;
    }

    public function setParentExecution(PvmExecutionImpl $parent): void
    {
        $this->parent = $parent;
    }

    // executions ///////////////////////////////////////////////////////////////
    public function getExecutionsAsCopy(): array
    {
        return $this->getExecutions();
    }

    /** ensures initialization and returns the non-null executions list */
    public function &getExecutions(): array
    {
        return $this->executions;
    }

    public function getSuperExecution(): ?DelegateExecutionInterface
    {
        return $this->superExecution;
    }

    public function setSuperExecution(?PvmExecutionImpl $superExecution): void
    {
        $this->superExecution = $superExecution;
        if ($this->superExecution !== null) {
            $this->superExecution->setSubProcessInstance(null);
        }
    }

    public function getSubProcessInstance(): ?ExecutionImpl
    {
        return $this->subProcessInstance;
    }

    public function setSubProcessInstance(?PvmExecutionImpl $subProcessInstance): void
    {
        $this->subProcessInstance = $subProcessInstance;
    }

    // super case execution /////////////////////////////////////////////////////

    /*public CaseExecutionImpl getSuperCaseExecution() {
        return superCaseExecution;
    }

    public void setSuperCaseExecution(CmmnExecution superCaseExecution) {
        this.superCaseExecution = (CaseExecutionImpl) superCaseExecution;
    }

    // sub case execution ////////////////////////////////////////////////////////

    public CaseExecutionImpl getSubCaseInstance() {
        return subCaseInstance;
    }

    public void setSubCaseInstance(CmmnExecution subCaseInstance) {
        this.subCaseInstance = (CaseExecutionImpl) subCaseInstance;
    }

    public CaseExecutionImpl createSubCaseInstance(CmmnCaseDefinition caseDefinition) {
        return createSubCaseInstance(caseDefinition, null);
    }

    public CaseExecutionImpl createSubCaseInstance(CmmnCaseDefinition caseDefinition, String businessKey) {
        CaseExecutionImpl caseInstance = (CaseExecutionImpl) caseDefinition.createCaseInstance(businessKey);

        // manage bidirectional super-process-sub-case-instances relation
        subCaseInstance->setSuperExecution(this);
        setSubCaseInstance(subCaseInstance);

        return caseInstance;
    }*/

    // process definition ///////////////////////////////////////////////////////

    public function getProcessDefinitionId(): ?string
    {
        return $this->processDefinition->getId();
    }

    // process instance /////////////////////////////////////////////////////////

    /** ensures initialization and returns the process instance. */
    public function getProcessInstance(): DelegateExecutionInterface
    {
        return $this->processInstance;
    }

    public function getProcessInstanceId(): ?string
    {
        return $this->getProcessInstance()->getId();
    }

    public function getBusinessKey(): ?string
    {
        return $this->getProcessInstance()->getBusinessKey();
    }

    public function setBusinessKey(?string $businessKey): void
    {
        $this->businessKey = $businessKey;
    }

    public function getProcessBusinessKey(): ?string
    {
        return $this->getProcessInstance()->getBusinessKey();
    }

    /** for setting the process instance, this setter must be used as subclasses can override */
    public function setProcessInstance(PvmExecutionImpl $processInstance): void
    {
        $this->processInstance = $processInstance;
    }

    // activity /////////////////////////////////////////////////////////////////

    /**
     * generates an activity instance id
     */
    protected function generateActivityInstanceId(?string $activityId): ?string
    {
        self::$idGenerator += 1;
        $nextId = self::$idGenerator;
        $compositeId = $activityId . ":" . $nextId;
        if (strlen($compositeId) > 64) {
            return strval($nextId);
        }
        return $compositeId;
    }

    // toString /////////////////////////////////////////////////////////////////

    public function __toString()
    {
        if ($this->isProcessInstanceExecution()) {
            return "ProcessInstance[" . $this->getToStringIdentity() . "]";
        } else {
            return ($this->isEventScope ? "EventScope" : "") . ($this->isConcurrent ? "Concurrent" : "") .
                   ($this->isScope() ? "Scope" : "") . "Execution[" . $this->getToStringIdentity() . "]";
        }
    }

    protected function getToStringIdentity(): ?string
    {
        return spl_object_hash($this);
    }

    // allow for subclasses to expose a real id /////////////////////////////////

    public function getId(): ?string
    {
        return spl_object_hash($this);
    }

    // getters and setters //////////////////////////////////////////////////////

    protected function getVariableStore(): VariableStore
    {
        return $this->variableStore;
    }

    protected function getVariableInstanceFactory(): VariableInstanceFactoryInterface
    {
        return SimpleVariableInstanceFactory::getInstance();
    }

    protected function getVariableInstanceLifecycleListeners(): array
    {
        return [];
    }

    public function getReplacedBy(): ?ExecutionImpl
    {
        return $this->replacedBy;
    }

    public function setExecutions(array $executions): void
    {
        $this->executions = $executions;
    }

    public function getCurrentActivityName(): ?string
    {
        $currentActivityName = null;
        if ($this->activity !== null) {
            $currentActivityName = $this->activity->getProperty("name");
        }
        return $currentActivityName;
    }

    public function getBpmnModelElementInstance(): ?FlowElementInterface
    {
        throw new \Exception(BpmnModelExecutionContextInterface::class . " is unsupported in transient ExecutionImpl");
    }

    public function getBpmnModelInstance(): BpmnModelInstanceInterface
    {
        throw new \Exception(BpmnModelExecutionContextInterface::class . " is unsupported in transient ExecutionImpl");
    }

    public function getProcessEngineServices(): ProcessEngineServicesInterface
    {
        throw new \Exception(ProcessEngineServicesAwareInterface::class . " is unsupported in transient ExecutionImpl");
    }

    public function getProcessEngine(): ProcessEngineInterface
    {
        throw new \Exception(ProcessEngineServicesAwareInterface::class . " is unsupported in transient ExecutionImpl");
    }

    public function forceUpdate(): void
    {
      // nothing to do
    }

    public function fireHistoricProcessStartEvent(): void
    {
      // do nothing
    }

    protected function removeVariablesLocalInternal(): void
    {
      // do nothing
    }
}
