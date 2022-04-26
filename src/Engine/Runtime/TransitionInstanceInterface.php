<?php

namespace Jabe\Engine\Runtime;

interface TransitionInstanceInterface extends ProcessElementInstanceInterface
{
    /**
     * returns the id of the activity a transition is made from/to
     */
    public function getActivityId(): string;

    /** returns the id of of the execution that is
     * executing this transition instance */
    public function getExecutionId(): string;

    /**
     * returns the type of the activity a transition is made from/to.
     * Corresponds to BPMN element name in XML (e.g. 'userTask').
     * The type of the root activity instance (the one corresponding to the process instance)
     * is 'processDefinition'.
     */
    public function getActivityType(): string;

    /**
     * returns the name of the activity a transition is made from/to
     */
    public function getActivityName(): string;

    /** the ids of currently open incidents */
    public function getIncidentIds(): array;

    /** the list of currently open incidents */
    public function getIncidents(): array;
}
