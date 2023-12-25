<?php

namespace Jabe\Impl\Application;

use Jabe\Application\{
    ProcessApplicationReferenceInterface,
    ProcessApplicationRegistrationInterface
};
use Jabe\Application\Impl\ProcessApplicationLogger;
use Jabe\Impl\Cfg\{
    ProcessEngineConfigurationImpl,
    TransactionState
};
use Jabe\Impl\Context\Context;
use Jabe\Impl\Interceptor\CommandContext;
use Jabe\Impl\Persistence\Deploy\DeleteDeploymentFailListener;
use Jabe\Impl\Persistence\Entity\{
    DeploymentEntity,
    ProcessDefinitionEntity,
    ProcessDefinitionManager
};
use Jabe\Engine\Repository\ProcessDefinitionInterface;

class ProcessApplicationManager
{
    //public final static ProcessApplicationLogger LOG = ProcessEngineLogger.PROCESS_APPLICATION_LOGGER;

    protected $registrationsByDeploymentId = [];

    public function getProcessApplicationForDeployment(?string $deploymentId): ?ProcessApplicationReferenceInterface
    {
        $registration = null;
        if (array_key_exists($deploymentId, $this->registrationsByDeploymentId)) {
            $registration = $this->registrationsByDeploymentId[$deploymentId];
        }
        if ($registration !== null) {
            return $registration->getReference();
        } else {
            return null;
        }
    }

    public function registerProcessApplicationForDeployments(array $deploymentsToRegister, ProcessApplicationReferenceInterface $reference): ProcessApplicationRegistrationInterface
    {
        // create process application registration
        $registration = $this->createProcessApplicationRegistration($deploymentsToRegister, $reference);
        // register with job executor
        $this->createJobExecutorRegistrations($deploymentsToRegister);
        //$this->logRegistration($deploymentsToRegister, $reference);
        return $registration;
    }

    public function clearRegistrations(): void
    {
        $this->registrationsByDeploymentId = [];
    }

    public function unregisterProcessApplicationForDeployments(array $deploymentIds, bool $removeProcessesFromCache): void
    {
        $this->removeJobExecutorRegistrations($deploymentIds);
        $this->removeProcessApplicationRegistration($deploymentIds, $removeProcessesFromCache);
    }

    public function hasRegistrations(): bool
    {
        return !empty($this->registrationsByDeploymentId);
    }

    protected function createProcessApplicationRegistration(array $deploymentsToRegister, ProcessApplicationReferenceInterface $reference): DefaultProcessApplicationRegistration
    {
        $processEngineName = Context::getProcessEngineConfiguration()->getProcessEngineName();

        $registration = new DefaultProcessApplicationRegistration($reference, $deploymentsToRegister, $processEngineName);
        // add to registration map
        foreach ($deploymentsToRegister as $deploymentId) {
            $this->registrationsByDeploymentId[$deploymentId] = $registration;
        }
        return $registration;
    }

    protected function removeProcessApplicationRegistration(array $deploymentIds, bool $removeProcessesFromCache): void
    {
        foreach ($deploymentIds as $deploymentId) {
            try {
                if ($removeProcessesFromCache) {
                    Context::getProcessEngineConfiguration()
                    ->getDeploymentCache()
                    ->removeDeployment($deploymentId);
                }
            } catch (\Throwable $t) {
                //LOG.couldNotRemoveDefinitionsFromCache(t);
            } finally {
                if ($deploymentId !== null && array_key_exists($deploymentId, $this->registrationsByDeploymentId)) {
                    unset($this->registrationsByDeploymentId[$deploymentId]);
                }
            }
        }
    }

    protected function createJobExecutorRegistrations(array $deploymentIds): void
    {
        try {
            $deploymentFailListener = new DeploymentFailListener(
                $deploymentIds,
                Context::getProcessEngineConfiguration()->getCommandExecutorTxRequiresNew()
            );
            Context::getCommandContext()
            ->getTransactionContext()
            ->addTransactionListener(TransactionState::ROLLED_BACK, $deploymentFailListener);

            $registeredDeployments = &Context::getProcessEngineConfiguration()->getRegisteredDeployments();
            $registeredDeployments = array_merge($registeredDeployments, $deploymentIds);
        } catch (\Throwable $e) {
            //throw LOG.exceptionWhileRegisteringDeploymentsWithJobExecutor(e);
            throw new \Exception("exceptionWhileRegisteringDeploymentsWithJobExecutor");
        }
    }

    protected function removeJobExecutorRegistrations(array $deploymentIds): void
    {
        try {
            $registeredDeployments = &Context::getProcessEngineConfiguration()->getRegisteredDeployments();
            foreach ($deploymentIds as $value) {
                //registeredDeployments.removeAll(deploymentIds);
                if (($id = array_search($value, $registeredDeployments)) !== false) {
                    unset($registeredDeployments[$id]);
                }
            }
        } catch (\Throwable $e) {
            //LOG.exceptionWhileUnregisteringDeploymentsWithJobExecutor(e);
            throw new \Exception("exceptionWhileUnregisteringDeploymentsWithJobExecutor");
        }
    }

    // logger ////////////////////////////////////////////////////////////////////////////

    /*protected void logRegistration(Set<String> deploymentIds, ProcessApplicationReference reference) {

        if (!LOG.isInfoEnabled()) {
            // building the log message is expensive (db queries) so we avoid it if we can
            return;
        }

        try {
            StringBuilder builder = new StringBuilder();
            builder.append("ProcessApplication '");
            builder.append(reference.getName());
            builder.append("' registered for DB deployments ");
            builder.append(deploymentIds);
            builder.append(". ");

            List<ProcessDefinition> processDefinitions = new ArrayList<ProcessDefinition>();
            List<CaseDefinition> caseDefinitions = new ArrayList<CaseDefinition>();

            CommandContext commandContext = Context.getCommandContext();
            ProcessEngineConfigurationImpl processEngineConfiguration = Context.getProcessEngineConfiguration();
            boolean cmmnEnabled = processEngineConfiguration.isCmmnEnabled();

            for (String deploymentId : deploymentIds) {

            DeploymentEntity deployment = commandContext
                .getDbEntityManager()
                .selectById(DeploymentEntity.class, deploymentId);

            if (deployment != null) {

                processDefinitions.addAll(getDeployedProcessDefinitionArtifacts(deployment));

                if (cmmnEnabled) {
                caseDefinitions.addAll(getDeployedCaseDefinitionArtifacts(deployment));
                }
            }
            }

            logProcessDefinitionRegistrations(builder, processDefinitions);

            if (cmmnEnabled) {
            logCaseDefinitionRegistrations(builder, caseDefinitions);
            }

            LOG.registrationSummary(builder.toString());

        }
        catch(Throwable e) {
            LOG.exceptionWhileLoggingRegistrationSummary(e);
        }
    }*/

    protected function getDeployedProcessDefinitionArtifacts(DeploymentEntity $deployment): array
    {
        $commandContext = Context::getCommandContext();

        // in case deployment was created by this command
        $entities = $deployment->getDeployedProcessDefinitions();

        if (empty($entities)) {
            $deploymentId = $deployment->getId();
            $manager = $commandContext->getProcessDefinitionManager();
            return $manager->findProcessDefinitionsByDeploymentId($deploymentId);
        }

        return $entities;
    }

    /*protected void logProcessDefinitionRegistrations(StringBuilder builder, List<ProcessDefinition> processDefinitions) {
        if (processDefinitions.isEmpty()) {
            builder.append("Deployment does not provide any process definitions.");
        } else {
            builder.append("Will execute process definitions ");
            builder.append("\n");
            for (ProcessDefinition processDefinition : processDefinitions) {
                builder.append("\n");
                builder.append("        ");
                builder.append(processDefinition.getKey());
                builder.append("[version: ");
                builder.append(processDefinition.getVersion());
                builder.append(", id: ");
                builder.append(processDefinition.getId());
                builder.append("]");
            }
            builder.append("\n");
        }
    }*/

    public function getRegistrationSummary(): ?string
    {
        $builder = "";
        foreach ($this->registrationsByDeploymentId as $key => $value) {
            if (strlen($builder) > 0) {
                $builder .= ", ";
            }
            $builder .= $key;
            $builder .= "->";
            $builder .= $value->getReference()->getName();
        }
        return $builder;
    }
}
