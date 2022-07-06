<?php

namespace Jabe\Engine\Impl\JobExecutor;

class AsyncContinuationConfiguration implements JobHandlerConfigurationInterface
{
    protected $atomicOperation;
    protected $transitionId;

    public function getAtomicOperation(): string
    {
        return $this->atomicOperation;
    }

    public function setAtomicOperation(string $atomicOperation): void
    {
        $this->atomicOperation = $atomicOperation;
    }

    public function getTransitionId(): string
    {
        return $this->transitionId;
    }

    public function setTransitionId(string $transitionId): void
    {
        $this->transitionId = $transitionId;
    }

    public function toCanonicalString(): string
    {
        $configuration = $this->atomicOperation;

        if ($this->transitionId !== null) {
            // store id of selected transition in case this is async after.
            // id is not serialized with the execution -> we need to remember it as
            // job handler configuration.
            $configuration .= '$' . $this->transitionId;
        }

        return $configuration;
    }

    public function __toString()
    {
        return $this->toCanonicalString();
    }
}
