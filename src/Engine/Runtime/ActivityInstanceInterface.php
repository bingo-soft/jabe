<?php

namespace Jabe\Engine\Runtime;

interface ActivityInstanceInterface extends ProcessElementInstanceInterface
{
    /** the id of the activity */
    public function getActivityId(): string;

    /** the name of the activity */
    public function getActivityName(): string;

    /**
     * Type of the activity, corresponds to BPMN element name in XML (e.g. 'userTask').
     * The type of the Root activity instance (the one corresponding to the process instance will be 'processDefinition'.
     */
    public function getActivityType(): string;

    /** Returns the child activity instances.
     * Returns an empty list if there are no child instances */
    public function getChildActivityInstances(): array;

    /** Returns the child transition instances.
     * Returns an empty list if there are no child transition instances */
    public function getChildTransitionInstances(): array;

    /** the list of executions that are currently waiting in this activity instance */
    public function getExecutionIds(): array;

    /**
     * all descendant (children, grandchildren, etc.) activity instances that are instances of the supplied activity
     */
    public function getActivityInstances(string $activityId): array;

    /**
     * all descendant (children, grandchildren, etc.) transition instances that are leaving or entering the supplied activity
     */
    public function getTransitionInstances(string $activityId): array;

    /** the ids of currently open incidents */
    public function getIncidentIds(): array;

    /** the list of currently open incidents */
    public function getIncidents(): array;
}
