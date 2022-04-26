<?php

namespace Jabe\Engine\Runtime;

use Jabe\Engine\Variable\Value\TypedValueInterface;

interface VariableInstanceInterface
{
    /**
     * @return the Id of this variable instance
     */
    public function getId(): string;

    /**
     * Returns the name of this variable instance.
     */
    public function getName(): string;

    /**
     * Returns the name of the type of this variable instance
     *
     * @return the type name of the variable
     */
    public function getTypeName(): string;

    /**
     * Returns the value of this variable instance.
     */
    public function getValue();

    /**
     * Returns the TypedValue of this variable instance.
     */
    public function getTypedValue(?bool $deserializeValue = null): TypedValueInterface;

    /**
     * Returns the corresponding process instance id.
     */
    public function getProcessInstanceId(): string;

    /**
     * Returns the corresponding execution id.
     */
    public function getExecutionId(): string;

    /**
     * Return the corresponding process definition id.
     */
    public function getProcessDefinitionId(): string;

    /**
     * Returns the corresponding case instance id.
     */
    public function getCaseInstanceId(): string;

    /**
     * Returns the corresponding case execution id.
     */
    public function getCaseExecutionId(): string;

    /**
     * Returns the corresponding task id.
     */
    public function getTaskId(): string;

    /**
     * Returns the corresponding batch id.
     */
    public function getBatchId(): string;

    /**
     * Returns the corresponding activity instance id.
     */
    public function getActivityInstanceId(): string;

    /**
     * If the variable value could not be loaded, this returns the error message.
     * @return an error message indicating why the variable value could not be loaded.
     */
    public function getErrorMessage(): string;

    /**
     * The id of the tenant this variable belongs to. Can be <code>null</code>
     * if the variable belongs to no single tenant.
     */
    public function getTenantId(): ?string;
}
