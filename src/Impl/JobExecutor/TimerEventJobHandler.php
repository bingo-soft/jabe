<?php

namespace Jabe\Impl\JobExecutor;

use Jabe\ProcessEngineException;
use Jabe\Impl\Persistence\Entity\JobEntity;

abstract class TimerEventJobHandler implements JobHandlerInterface
{
    public const JOB_HANDLER_CONFIG_PROPERTY_DELIMITER = '$';
    public const JOB_HANDLER_CONFIG_PROPERTY_FOLLOW_UP_JOB_CREATED = "followUpJobCreated";
    public const JOB_HANDLER_CONFIG_TASK_LISTENER_PREFIX = "taskListener~";

    public function newConfiguration(?string $canonicalString): JobHandlerConfigurationInterface
    {
        $configParts = explode('\\' . self::JOB_HANDLER_CONFIG_PROPERTY_DELIMITER, $canonicalString);

        if (count($configParts) > 3) {
            throw new ProcessEngineException("Illegal timer job handler configuration: '" . $canonicalString
                . "': exprecting a one, two or three part configuration seperated by '" . self::JOB_HANDLER_CONFIG_PROPERTY_DELIMITER . "'.");
        }

        $configuration = new TimerJobConfiguration();
        $configuration->setTimerElementKey($configParts[0]);

        // depending on the job configuration, the next parts can be a task listener id and/or the follow-up-job flag
        for ($i = 1; $i < count($configParts); $i += 1) {
            $this->adjustConfiguration($configuration, $configParts[i]);
        }

        return $configuration;
    }

    protected function adjustConfiguration(TimerJobConfiguration $configuration, ?string $configPart): void
    {
        if (strpos($configPart, self::JOB_HANDLER_CONFIG_TASK_LISTENER_PREFIX) === 0) {
            $configuration->setTimerElementSecondaryKey(substr($configPart, strlen(self::JOB_HANDLER_CONFIG_TASK_LISTENER_PREFIX)));
        } else {
            $configuration->followUpJobCreated = self::JOB_HANDLER_CONFIG_PROPERTY_FOLLOW_UP_JOB_CREATED == $configPart;
        }
    }

    public function onDelete(JobHandlerConfigurationInterface $configuration, JobEntity $jobEntity): void
    {
        // do nothing
    }
}
