<?php

namespace Jabe\Engine\Migration;

interface MigrationPlanInterface
{
    /**
     * @return array the list of instructions that this plan consists of
     */
    public function getInstructions(): array;

    /**
     * @return string the id of the process definition that is migrated from
     */
    public function getSourceProcessDefinitionId(): string;

    /**
     * @return string the id of the process definition that is migrated to
     */
    public function getTargetProcessDefinitionId(): string;
}
