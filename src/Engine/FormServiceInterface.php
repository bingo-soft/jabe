<?php

namespace Jabe\Engine;

use Jabe\Engine\Authorization\{
    Permissions,
    Resources
};
use Jabe\Engine\Form\{
    StartFormDataInterface,
    TaskFormDataInterface
};
use Jabe\Engine\Runtime\{
    ProcessInstanceInterface,
    ProcessInstanceQueryInterface
};
use Jabe\Engine\Task\{
    TaskInterface,
    TaskQueryInterface
};
use Jabe\Engine\Variable\VariableMapInterface;
use Jabe\Engine\Variable\Value\SerializableValueInterface;

interface FormServiceInterface
{
    /**
     * Retrieves all data necessary for rendering a form to start a new process instance.
     * This can be used to perform rendering of the forms outside of the process engine.
     *
     * @throws AuthorizationException
     *          If the user has no {@link Permissions#READ} permission on {@link Resources#PROCESS_DEFINITION}.
     */
    public function getStartFormData(string $processDefinitionId): StartFormDataInterface;

    /**
     * Rendered form generated by the given build-in form engine for starting a new process instance.
     *
     * @throws AuthorizationException
     *          If the user has no {@link Permissions#READ} permission on {@link Resources#PROCESS_DEFINITION}.
     */
    public function getRenderedStartForm(string $processDefinitionId, ?string $formEngineName = null);

    /**
     * Start a new process instance with the user data that was entered as properties in a start form.
     *
     * A business key can be provided to associate the process instance with a
     * certain identifier that has a clear business meaning. For example in an
     * order process, the business key could be an order id. This business key can
     * then be used to easily look up that process instance , see
     * {@link ProcessInstanceQuery#processInstanceBusinessKey(String)}. Providing such a business
     * key is definitely a best practice.
     *
     * Note that a business key MUST be unique for the given process definition.
     * Process instance from different process definition are allowed to have the
     * same business key.
     *
     * @param processDefinitionId the id of the process definition, cannot be null.
     * @param businessKey a key that uniquely identifies the process instance in the context or the
     *                    given process definition.
     * @param properties the properties to pass, can be null.
     *
     * @throws AuthorizationException
     *          If the user has no {@link Permissions#CREATE} permission on {@link Resources#PROCESS_INSTANCE}
     *          and no {@link Permissions#CREATE_INSTANCE} permission on {@link Resources#PROCESS_DEFINITION}.
     */
    public function submitStartForm(string $processDefinitionId, array $properties, ?string $businessKey = null): ProcessInstanceInterface;

    /**
     * Retrieves all data necessary for rendering a form to complete a task.
     * This can be used to perform rendering of the forms outside of the process engine.
     *
     * @throws AuthorizationException
     *          <p>In case of standalone tasks:
     *          <li>if the user has no {@link Permissions#READ} permission on {@link Resources#TASK} or</li>
     *          <li>if the user has no {@link TaskPermissions#READ_VARIABLE} permission on {@link Resources#TASK}</li></p>
     *          <p>In case the task is part of a running process instance:</li>
     *          <li>if the user has no {@link Permissions#READ} permission on {@link Resources#TASK} or
     *           no {@link Permissions#READ_TASK} permission on {@link Resources#PROCESS_DEFINITION} </li>
     *          <li>if the user has {@link TaskPermissions#READ_VARIABLE} permission on {@link Resources#TASK} or
     *          no {@link ProcessDefinitionPermissions#READ_TASK_VARIABLE} permission on {@link Resources#PROCESS_DEFINITION}
     *          when {@link ProcessEngineConfiguration#enforceSpecificVariablePermission this} config is enabled</li>
     *          </ul></p>
     */
    public function getTaskFormData(string $taskId): TaskFormDataInterface;

    /**
     * Rendered form generated by the given build-in form engine for completing a task.
     *
     * @throws AuthorizationException
     *          <p>In case of standalone tasks:
     *          <li>if the user has no {@link Permissions#READ} permission on {@link Resources#TASK} or</li>
     *          <li>if the user has no {@link TaskPermissions#READ_VARIABLE} permission on {@link Resources#TASK}
     *          when {@link ProcessEngineConfiguration#enforceSpecificVariablePermission this} config is enabled</li></p>
     *          <p>In case the task is part of a running process instance:</li>
     *          <li>if the user has no {@link Permissions#READ} permission on {@link Resources#TASK} or
     *           no {@link Permissions#READ_TASK} permission on {@link Resources#PROCESS_DEFINITION} </li>
     *          <li>if the user has {@link TaskPermissions#READ_VARIABLE} permission on {@link Resources#TASK} or
     *          no {@link ProcessDefinitionPermissions#READ_TASK_VARIABLE} permission on {@link Resources#PROCESS_DEFINITION}
     *          when {@link ProcessEngineConfiguration#enforceSpecificVariablePermission this} config is enabled</li></p>
     */
    public function getRenderedTaskForm(string $taskId, ?string $formEngineName = null);

    /**
     * Completes a task with the user data that was entered as properties in a task form.
     *
     * @param taskId
     * @param properties
     *
     * @throws AuthorizationException
     *          If the user has no {@link Permissions#UPDATE} permission on {@link Resources#TASK}
     *          or no {@link Permissions#UPDATE_TASK} permission on {@link Resources#PROCESS_DEFINITION}.
     */
    public function submitTaskForm(string $taskId, array $properties): void;

    /**
     * Completes a task with the user data that was entered as properties in a task form.
     *
     * @param taskId
     * @param properties
     * @param deserializeValues if false, returned {@link SerializableValue}s
     *   will not be deserialized (unless they are passed into this method as a
     *   deserialized value or if the BPMN process triggers deserialization)
     * @return a map of process variables
     *
     * @throws AuthorizationException
     *          If the user has no {@link Permissions#UPDATE} permission on {@link Resources#TASK}
     *          or no {@link Permissions#UPDATE_TASK} permission on {@link Resources#PROCESS_DEFINITION}.
     */
    public function submitTaskFormWithVariablesInReturn(string $taskId, array $properties, bool $deserializeValues): VariableMapInterface;

    /**
     * Retrieves a list of requested variables for rendering a start from. The method takes into account
     * FormData specified for the start event. This allows defining default values for form fields.
     *
     * @param processDefinitionId the id of the process definition for which the start form should be retrieved.
     * @param formVariables a Collection of the names of the variables to retrieve. Allows restricting the set of retrieved variables.
     * @param deserializeObjectValues if false object values are not deserialized
     * @return a map of VariableInstances.
     *
     * @throws AuthorizationException
     *          If the user has no {@link Permissions#READ} permission on {@link Resources#PROCESS_DEFINITION}.
     */
    public function getStartFormVariables(string $processDefinitionId, ?array $formVariables = null, ?bool $deserializeObjectValues = null): VariableMapInterface;

    /**
     * <p>Retrieves a list of requested variables for rendering a task form. In addition to the task variables and process variables,
     * the method takes into account FormData specified for the task. This allows defining default values for form fields.</p>
     *
     * <p>A variable is resolved in the following order:
     * <ul>
     *   <li>First, the method collects all form fields and creates variable instances for the form fields.</li>
     *   <li>Next, the task variables are collected.</li>
     *   <li>Next, process variables from the parent scopes of the task are collected, until the process instance scope is reached.</li>
     * </ul>
     * </p>
     *
     * @param taskId the id of the task for which the variables should be retrieved.
     * @param formVariables a Collection of the names of the variables to retrieve. Allows restricting the set of retrieved variables.
     * @param deserializeObjectValues if false object values are not deserialized
     * @return a map of VariableInstances.
     *
     * @throws AuthorizationException
     *          <p>In case of standalone tasks:
     *          <li>if the user has no {@link Permissions#READ} permission on {@link Resources#TASK} or</li>
     *          <li>if the user has no {@link TaskPermissions#READ_VARIABLE} permission on {@link Resources#TASK}
     *          when {@link ProcessEngineConfiguration#enforceSpecificVariablePermission this} config is enabled</li></p>
     *          <p>In case the task is part of a running process instance:</li>
     *          <li>if the user has no {@link Permissions#READ} permission on {@link Resources#TASK} or
     *           no {@link Permissions#READ_TASK} permission on {@link Resources#PROCESS_DEFINITION} </li>
     *          <li>if the user has {@link TaskPermissions#READ_VARIABLE} permission on {@link Resources#TASK} or
     *          no {@link ProcessDefinitionPermissions#READ_TASK_VARIABLE} permission on {@link Resources#PROCESS_DEFINITION}
     *          when {@link ProcessEngineConfiguration#enforceSpecificVariablePermission this} config is enabled</li></p>
     */
    public function getTaskFormVariables(string $taskId, ?array $formVariables = null, ?bool $deserializeObjectValues = null): VariableMapInterface;

    /**
     * Retrieves a user defined reference to a start form.
     *
     * In the Explorer app, it is assumed that the form key specifies a resource
     * in the deployment, which is the template for the form.  But users are free
     * to use this property differently.
     *
     * @throws AuthorizationException
     *          If the user has no {@link Permissions#READ} permission on {@link Resources#PROCESS_DEFINITION}.
     */
    public function getStartFormKey(string $processDefinitionId): string;

    /**
     * Retrieves a user defined reference to a task form.
     *
     * In the Explorer app, it is assumed that the form key specifies a resource
     * in the deployment, which is the template for the form.  But users are free
     * to use this property differently.
     *
     * Both arguments can be obtained from {@link Task} instances returned by any
     * {@link TaskQuery}.
     *
     * @throws AuthorizationException
     *          If the user has no {@link Permissions#READ} permission on {@link Resources#PROCESS_DEFINITION}.
     */
    public function getTaskFormKey(string $processDefinitionId, string $taskDefinitionKey): string;

    /**
     * Retrieves a deployed start form for a process definition with a given id.
     *
     *
     * @throws AuthorizationException
     *          If the user has no {@link Permissions#READ} permission on {@link Resources#PROCESS_DEFINITION}.
     * @throws NotFoundException
     *          If the start form cannot be found.
     * @throws BadUserRequestException
     *          If the start form key has wrong format ("embedded:deployment:<path>" or "deployment:<path>" required).
     */
    public function getDeployedStartForm(string $processDefinitionId);

    /**
     * Retrieves a deployed task form for a task with a given id.
     *
     *
     * @throws AuthorizationException
     *          If the user has no {@link Permissions#READ} permission on {@link Resources#TASK}.
     * @throws NotFoundException
     *          If the task form cannot be found.
     * @throws BadUserRequestException
     *          If the task form key has wrong format ("embedded:deployment:<path>" or "deployment:<path>" required).
     */
    public function getDeployedTaskForm(string $taskId);
}
