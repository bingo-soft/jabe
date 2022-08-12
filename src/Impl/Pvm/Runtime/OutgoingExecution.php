<?php

namespace Jabe\Impl\Pvm\Runtime;

use Jabe\Impl\Pvm\{
    PvmLogger,
    PvmTransitionInterface
};

class OutgoingExecution
{
    //private final static PvmLogger LOG = PvmLogger.PVM_LOGGER;

    protected $outgoingExecution;
    protected $outgoingTransition;

    public function __construct(PvmExecutionImpl $outgoingExecution, PvmTransitionInterface $outgoingTransition)
    {
        $this->outgoingExecution = $outgoingExecution;
        $this->outgoingTransition = $outgoingTransition;
        $this->outgoingExecution->setTransition($outgoingTransition);
        $this->outgoingExecution->setActivityInstanceId(null);
    }

    public function take(): void
    {
        if ($this->outgoingExecution->getReplacedBy() !== null) {
            $this->outgoingExecution = $this->outgoingExecution->getReplacedBy();
        }
        if (!$this->outgoingExecution->isEnded()) {
            $this->outgoingExecution->take();
        } else {
            //LOG.notTakingTranistion(outgoingTransition);
        }
    }

    public function getOutgoingExecution(): PvmExecutionImpl
    {
        return $this->outgoingExecution;
    }
}
