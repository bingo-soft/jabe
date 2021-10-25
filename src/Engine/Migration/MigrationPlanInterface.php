<?php

namespace BpmPlatform\Engine\Migration;

interface MigrationPlanInterface
{
    /**
     * @return the list of instructions that this plan consists of
     */
    public function getInstructions(): array;

    /**
     * @return the id of the process definition that is migrated from
     */
    public function getSourceProcessDefinitionId(): string;

    /**
     * @return the id of the process definition that is migrated to
     */
    public function getTargetProcessDefinitionId(): string;
}
