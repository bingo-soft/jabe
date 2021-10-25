<?php

namespace BpmPlatform\Engine\Migration;

interface MigrationInstructionsBuilderInterface extends MigrationPlanBuilderInterface
{
    /**
     * Toggle whether the instructions should include updating of the respective event triggers
     * where appropriate. See {@link MigrationInstructionBuilder#updateEventTrigger()} for details
     * what updating the event trigger means for a single instruction.
     *
     * @return this builder
     */
    public function updateEventTriggers(): MigrationInstructionsBuilderInterface;
}
