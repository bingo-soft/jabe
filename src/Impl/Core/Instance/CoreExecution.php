<?php

namespace Jabe\Impl\Core\Instance;

use Jabe\Delegate\{
    BaseDelegateExecutionInterface,
    DelegateListenerInterface
};
use Jabe\Impl\Core\Model\CoreModelElement;
use Jabe\Impl\Core\Operation\CoreAtomicOperation;
use Jabe\Impl\Core\Variable\Scope\AbstractVariableScope;

abstract class CoreExecution extends AbstractVariableScope implements BaseDelegateExecutionInterface
{
    //private final static CoreLogger LOG = CoreLogger.CORE_LOGGER;

    protected $id;

    /**
     * the business key for this execution
     */
    protected $businessKey;
    protected $businessKeyWithoutCascade;

    protected $tenantId;

    // events ///////////////////////////////////////////////////////////////////

    protected $eventName;
    protected $eventSource;
    protected int $listenerIndex = 0;
    protected bool $skipCustomListeners = false;
    protected bool $skipIoMapping = false;
    protected bool $skipSubprocesses = false;

    // atomic operations ////////////////////////////////////////////////////////
    public function performOperation(/*CoreAtomicOperation*/$operation): void
    {
        //LOG.debugPerformingAtomicOperation(operation, this);
        $operation->execute($this);
    }

    public function performOperationSync(/*CoreAtomicOperation*/$operation): void
    {
        //LOG.debugPerformingAtomicOperation(operation, this);
        $operation->execute($this);
    }

    // event handling ////////////////////////////////////////////////////////

    public function getEventName(): ?string
    {
        return $this->eventName;
    }

    public function setEventName(?string $eventName): void
    {
        $this->eventName = $eventName;
    }

    public function getEventSource(): ?CoreModelElement
    {
        return $this->eventSource;
    }

    public function setEventSource(?CoreModelElement $eventSource): void
    {
        $this->eventSource = $eventSource;
    }

    public function getListenerIndex(): int
    {
        return $this->listenerIndex;
    }

    public function setListenerIndex(int $listenerIndex): void
    {
        $this->listenerIndex = $listenerIndex;
    }

    public function invokeListener(DelegateListenerInterface $listener): void
    {
        $listener->notify($this);
    }

    // getters / setters /////////////////////////////////////////////////

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    public function getBusinessKeyWithoutCascade(): ?string
    {
        return $this->businessKeyWithoutCascade;
    }

    public function setBusinessKey(?string $businessKey): void
    {
        $this->businessKey = $businessKey;
        $this->businessKeyWithoutCascade = $businessKey;
    }

    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }

    public function setTenantId(?string $tenantId): void
    {
        $this->tenantId = $tenantId;
    }

    public function isSkipCustomListeners(): bool
    {
        return $this->skipCustomListeners;
    }

    public function setSkipCustomListeners(bool $skipCustomListeners): void
    {
        $this->skipCustomListeners = $skipCustomListeners;
    }

    public function isSkipIoMappings(): bool
    {
        return $this->skipIoMapping;
    }

    public function setSkipIoMappings(bool $skipIoMappings): void
    {
        $this->skipIoMapping = $skipIoMappings;
    }

    public function isSkipSubprocesses(): bool
    {
        return $this->skipSubprocesses;
    }

    public function setSkipSubprocesseses(bool $skipSubprocesses): void
    {
        $this->skipSubprocesses = $skipSubprocesses;
    }
}
