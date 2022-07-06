<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\BadUserRequestException;
use Jabe\Engine\Exception\NullValueException;
use Jabe\Engine\Impl\Cfg\ProcessEngineConfigurationImpl;
use Jabe\Engine\Impl\Core\Variable\VariableUtil;
use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Engine\Impl\Migration\{
    MigrationInstructionGeneratorInterface,
    MigrationLogger,
    MigrationPlanBuilderImpl,
    MigrationPlanImpl
};
use Jabe\Engine\Impl\Migration\Validation\Instruction\{
    MigrationInstructionValidationReportImpl,
    MigrationInstructionValidatorInterface,
    MigrationPlanValidationReportImpl,
    MigrationVariableValidationReportImpl,
    ValidatingMigrationInstructionInterface,
    ValidatingMigrationInstructionImpl,
    ValidatingMigrationInstructions
};
use Jabe\Engine\Impl\Persistence\Entity\ProcessDefinitionEntity;
use Jabe\Engine\Impl\Pvm\Process\{
    ActivityImpl,
    ProcessDefinitionImpl
};
use Jabe\Engine\Impl\Util\{
    EngineUtilLogger,
    EnsureUtil
};
use Jabe\Engine\Migration\{
    MigrationInstructionInterface,
    MigrationPlanInterface
};
use Jabe\Engine\Variable\VariableMapInterface;
use Jabe\Engine\Variable\Value\TypedValueInterface;

class CreateMigrationPlanCmd implements CommandInterface
{
    //public static final MigrationLogger LOG = EngineUtilLogger.MIGRATION_LOGGER;

    protected $migrationBuilder;

    public function __construct(MigrationPlanBuilderImpl $migrationPlanBuilderImpl)
    {
        $this->migrationBuilder = $migrationPlanBuilderImpl;
    }

    public function execute(CommandContext $commandContext)
    {
        $sourceProcessDefinition = $this->getProcessDefinition($commandContext, $this->migrationBuilder->getSourceProcessDefinitionId(), "Source");
        $targetProcessDefinition = $this->getProcessDefinition($commandContext, $this->migrationBuilder->getTargetProcessDefinitionId(), "Target");

        $this->checkAuthorization($commandContext, $sourceProcessDefinition, $targetProcessDefinition);

        $migrationPlan = new MigrationPlanImpl($sourceProcessDefinition->getId(), $targetProcessDefinition->getId());
        $instructions = [];

        if ($this->migrationBuilder->isMapEqualActivities()) {
            $instructions = $this->generateInstructions($commandContext, $sourceProcessDefinition, $targetProcessDefinition, $this->migrationBuilder->isUpdateEventTriggersForGeneratedInstructions());
        }

        $instructions = array_merge($instructions, $this->migrationBuilder->getExplicitMigrationInstructions());
        $migrationPlan->setInstructions($instructions);

        $variables = $this->migrationBuilder->getVariables();
        if (!empty($variables)) {
            $migrationPlan->setVariables($variables);
        }

        $this->validateMigration($commandContext, $migrationPlan, $sourceProcessDefinition, $targetProcessDefinition);

        return $migrationPlan;
    }

    protected function validateMigration(
        CommandContext $commandContext,
        MigrationPlanImpl $migrationPlan,
        ProcessDefinitionEntity $sourceProcessDefinition,
        ProcessDefinitionEntity $targetProcessDefinition
    ): void {
        $planReport = new MigrationPlanValidationReportImpl($migrationPlan);

        $variables = $migrationPlan->getVariables();
        if (!empty($variables)) {
            $this->validateVariables($variables, $planReport);
        }

        $this->validateMigrationInstructions(
            $commandContext,
            $planReport,
            $migrationPlan,
            $sourceProcessDefinition,
            $targetProcessDefinition
        );

        if ($planReport->hasReports()) {
            //throw LOG.failingMigrationPlanValidation(planReport);
            throw new \Exception("failingMigrationPlanValidation");
        }
    }

    protected function validateVariables(
        VariableMapInterface $variables,
        MigrationPlanValidationReportImpl $planReport
    ): void {
        foreach ($variables->keySet() as $name) {
            $valueTyped = $variables->getValueTyped($name);

            $phpSerializationProhibited = VariableUtil::isPhpSerializationProhibited($valueTyped);
            if ($phpSerializationProhibited) {
                $report = new MigrationVariableValidationReportImpl($valueTyped);
                //$failureMessage = sprintf(VariableUtil::ERROR_MSG, $name);
                $failureMessage = $name;
                $report->addFailure($failureMessage);

                $planReport->addVariableReport($name, $report);
            }
        };
    }

    protected function getProcessDefinition(CommandContext $commandContext, ?string $id, string $type): ProcessDefinitionEntity
    {
        EnsureUtil::ensureNotNull(BadUserRequestException::class, $type . " process definition id", $id);
        try {
            return $commandContext->getProcessEngineConfiguration()
            ->getDeploymentCache()->findDeployedProcessDefinitionById($id);
        } catch (\Exception $e) {
            //throw LOG.processDefinitionDoesNotExist(id, type);
            throw new \Exception("processDefinitionDoesNotExist");
        }
    }

    protected function checkAuthorization(
        CommandContext $commandContext,
        ProcessDefinitionEntity $sourceProcessDefinition,
        ProcessDefinitionEntity $targetProcessDefinition
    ): void {
        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkCreateMigrationPlan($sourceProcessDefinition, $targetProcessDefinition);
        }
    }

    protected function generateInstructions(
        CommandContext $commandContext,
        ProcessDefinitionImpl $sourceProcessDefinition,
        ProcessDefinitionImpl $targetProcessDefinition,
        bool $updateEventTriggers
    ): array {
        $processEngineConfiguration = $commandContext->getProcessEngineConfiguration();

        // generate instructions
        $migrationInstructionGenerator = $processEngineConfiguration->getMigrationInstructionGenerator();
        $generatedInstructions = $migrationInstructionGenerator->generate($sourceProcessDefinition, $targetProcessDefinition, $updateEventTriggers);

        // filter only valid instructions
        $generatedInstructions->filterWith($processEngineConfiguration->getMigrationInstructionValidators());

        return $generatedInstructions->asMigrationInstructions();
    }

    protected function validateMigrationInstructions(
        CommandContext $commandContext,
        MigrationPlanValidationReportImpl $planReport,
        MigrationPlanImpl $migrationPlan,
        ProcessDefinitionImpl $sourceProcessDefinition,
        ProcessDefinitionImpl $targetProcessDefinition
    ): void {
        $migrationInstructionValidators = $commandContext->getProcessEngineConfiguration()->getMigrationInstructionValidators();

        $validatingMigrationInstructions = $this->wrapMigrationInstructions($migrationPlan, $sourceProcessDefinition, $targetProcessDefinition, $planReport);

        foreach ($validatingMigrationInstructions->getInstructions() as $validatingMigrationInstruction) {
            $instructionReport = $this->validateInstruction($validatingMigrationInstruction, $validatingMigrationInstructions, $migrationInstructionValidators);
            if ($instructionReport->hasFailures()) {
                $planReport->addInstructionReport($instructionReport);
            }
        }
    }

    protected function validateInstruction(
        ValidatingMigrationInstructionInterface $instruction,
        ValidatingMigrationInstructions $instructions,
        array $migrationInstructionValidators
    ): MigrationInstructionValidationReportImpl {
        $validationReport = new MigrationInstructionValidationReportImpl($instruction->toMigrationInstruction());
        foreach ($migrationInstructionValidators as $migrationInstructionValidator) {
            $migrationInstructionValidator->validate($instruction, $instructions, $validationReport);
        }
        return $validationReport;
    }

    protected function wrapMigrationInstructions(
        MigrationPlanInterface $migrationPlan,
        ProcessDefinitionImpl $sourceProcessDefinition,
        ProcessDefinitionImpl $targetProcessDefinition,
        MigrationPlanValidationReportImpl $planReport
    ): ValidatingMigrationInstructions {
        $validatingMigrationInstructions = new ValidatingMigrationInstructions();
        foreach ($migrationPlan->getInstructions() as $migrationInstruction) {
            $instructionReport = new MigrationInstructionValidationReportImpl($migrationInstruction);

            $sourceActivityId = $migrationInstruction->getSourceActivityId();
            $targetActivityId = $migrationInstruction->getTargetActivityId();
            if ($sourceActivityId !== null && $targetActivityId !== null) {
                $sourceActivity = $sourceProcessDefinition->findActivity($sourceActivityId);
                $targetActivity = $targetProcessDefinition->findActivity($migrationInstruction->getTargetActivityId());

                if ($sourceActivity !== null && $targetActivity !== null) {
                    $validatingMigrationInstructions->addInstruction(
                        new ValidatingMigrationInstructionImpl($sourceActivity, $targetActivity, $migrationInstruction->isUpdateEventTrigger())
                    );
                } else {
                    if ($sourceActivity === null) {
                        $instructionReport->addFailure("Source activity '" . $sourceActivityId . "' does not exist");
                    }
                    if ($targetActivity === null) {
                        $instructionReport->addFailure("Target activity '" . $targetActivityId . "' does not exist");
                    }
                }
            } else {
                if ($sourceActivityId === null) {
                    $instructionReport->addFailure("Source activity id is null");
                }
                if ($targetActivityId === null) {
                    $instructionReport->addFailure("Target activity id is null");
                }
            }

            if ($instructionReport->hasFailures()) {
                $planReport->addInstructionReport($instructionReport);
            }
        }
        return $validatingMigrationInstructions;
    }
}
