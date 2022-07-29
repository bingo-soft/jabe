<?php

namespace Jabe\Engine\History;

use Jabe\Engine\Variable\Value\TypedValueInterface;

interface HistoricVariableInstanceInterface
{
    public const STATE_CREATED = "CREATED";
    public const STATE_DELETED = "DELETED";

    /**
     * @return string the id of this variable instance
     */
    public function getId(): string;

    /**
     * Returns the name of this variable instance.
     */
    public function getName(): string;

    /**
     * Returns the name of the type of this variable instance
     *
     * @return string the type name of the variable
     */
    public function getTypeName(): string;

    /**
     * Returns the value of this variable instance.
     */
    public function getValue();

    /**
     * Returns the TypedValue of this variable instance.
     */
    public function getTypedValue(): TypedValueInterface;

    /**
     * The process definition key reference.
     */
    public function getProcessDefinitionKey(): string;

    /**
     * The process definition reference.
     */
    public function getProcessDefinitionId(): string;

    /**
     * The root process instance reference.
     */
    public function getRootProcessInstanceId(): string;

    /**
     * The process instance reference.
     */
    public function getProcessInstanceId(): string;

    /**
     * Return the corresponding execution id.
     */
    public function getExecutionId(): string;

    /**
     * Returns the corresponding activity instance id.
     */
    public function getActivityInstanceId(): string;

    /**
     * Return the corresponding task id.
     */
    public function getTaskId(): string;

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

    /**
     * The current state of the variable. Can be 'CREATED' or 'DELETED'
     */
    public function getState(): string;

    /**
     * The time when the variable was created.
     */
    public function getCreateTime(): string;

    /** The time when the historic variable instance will be removed. */
    public function getRemovalTime(): string;
}
