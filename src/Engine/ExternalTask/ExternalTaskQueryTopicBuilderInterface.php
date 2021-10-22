<?php

namespace BpmPlatform\Engine\ExternalTask;

interface ExternalTaskQueryTopicBuilderInterface extends ExternalTaskQueryBuilderInterface
{
    /**
     * Define variables to fetch with all tasks for the current topic. Calling
     * this method multiple times overrides the previously specified variables.
     *
     * @param variables the variable names to fetch, if null all variables will be fetched
     * @return this builder
     */
    public function variables(array $variables): ExternalTaskQueryTopicBuilderInterface;

    /**
     * Define a HashMap of variables and their values to filter correlated tasks.
     * Calling this method multiple times overrides the previously specified variables.
     *
     * @param variables a HashMap of the variable names (keys) and the values to filter by
     * @return this builder
     */
    public function processInstanceVariableEquals(array $variables): ExternalTaskQueryTopicBuilderInterface;

    /**
     * Define business key value to filter external tasks by (Process Instance) Business Key.
     *
     * @param businessKey the value of the Business Key to filter by
     * @return this builder
     */
    public function businessKey(string $businessKey): ExternalTaskQueryTopicBuilderInterface;

    /**
     * Define process definition id to filter external tasks by.
     *
     * @param processDefinitionId the definition id to filter by
     * @return this builder
     */
    public function processDefinitionId(string $processDefinitionId): ExternalTaskQueryTopicBuilderInterface;

    /**
     * Define process definition ids to filter external tasksb by.
     *
     * @param processDefinitionIds the definition ids to filter by
     * @return this builder
     */
    public function processDefinitionIdIn(array $processDefinitionIds): ExternalTaskQueryTopicBuilderInterface;

    /**
     * Define process definition key to filter external tasks by.
     *
     * @param processDefinitionKey the definition key to filter by
     * @return this builder
     */
    public function processDefinitionKey(string $processDefinitionKey): ExternalTaskQueryTopicBuilderInterface;

    /**
     * Define process definition keys to filter external tasks by.
     *
     * @param processDefinitionKey the definition keys to filter by
     * @return this builder
     */
    public function processDefinitionKeyIn(array $processDefinitionKeys): ExternalTaskQueryTopicBuilderInterface;


    /**
     * Define a process definition version tag to filter external tasks by.
     *
     * @param versionTag the version tag to filter by
     * @return this builder
     */
    public function processDefinitionVersionTag(string $versionTag): ExternalTaskQueryTopicBuilderInterface;

    /**
     * Filter external tasks only with null tenant id.
     *
     * @return this builder
     */
    public function withoutTenantId(): ExternalTaskQueryTopicBuilderInterface;

    /**
     * Define tenant ids to filter external tasks by.
     *
     * @param tenantIds the tenant ids to filter by
     * @return this builder
     */
    public function tenantIdIn(array $tenantIds): ExternalTaskQueryTopicBuilderInterface;

    /**
     * Enable deserialization of variable values that are custom objects. By default, the query
     * will not attempt to deserialize the value of these variables.
     *
     * @return this builder
     */
    public function enableCustomObjectDeserialization(): ExternalTaskQueryTopicBuilderInterface;

    /**
     * Define whether only local variables will be fetched with all tasks for the current topic.
     *
     * @return this builder
     */
    public function localVariables(): ExternalTaskQueryTopicBuilderInterface;

    /**
     * Configure the query to include custom extension properties, if available, for all fetched tasks.
     *
     * @return this builder
     */
    public function includeExtensionProperties(): ExternalTaskQueryTopicBuilderInterface;
}
