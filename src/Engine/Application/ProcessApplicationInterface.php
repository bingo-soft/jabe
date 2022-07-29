<?php

namespace Jabe\Engine\Application;

use Jabe\Engine\Impl\Util\El\{
    ELResolver,
    BeanELResolver
};
use Jabe\Engine\Repository\DeploymentBuilderInterface;
use Jabe\Engine\Delegate\{
    ExecutionListenerInterface,
    TaskListenerInterface
};

interface ProcessApplicationInterface
{
    /**
     * <p>Deploy this process application.</p>
     */
    public function deploy(): void;

    /**
     * <p>Undeploy this process application.</p>
     */
    public function undeploy(): void;

    /**
     * @return string the name of this process application
     */
    public function getName(): string;

    /**
     * <p>Returns a globally sharable reference to this process application. This reference may be safely passed
     * to the process engine. And other applications.</p>
     *
     * @return a globally sharable reference to this process application.
     */
    public function getReference(): ?ProcessApplicationReferenceInterface;

    /**
     * Since {@link #getReference()} may return a proxy object, this method returs the actual, unproxied object and is
     * meant to be called from the {@link #execute(Callable)} method. (ie. from a Callable implementation passed to
     * the method.).
     */
    public function getRawObject(): ProcessApplicationInterface;

    /**
     * The default implementation simply modifies the Context ClassLoader
     *
     * @param callable to be executed "within" the context of this process application.
     * @param context of the current invocation, can be <code>null</code>
     * @return mixed the result of the callback
     */
    public function execute(callable $callable, ?InvocationContext $context = null);

    /**
     * <p>override this method in order to provide a map of properties.</p>
     *
     * <p>The properties are made available globally through the ProcessApplicationService</p>
     *
     * @see ProcessApplicationService
     * @see ProcessApplicationInfo#getProperties()
     */
    public function getProperties(): array;

    /**
     * <p>This allows the process application to provide a custom ElResolver to the process engine.</p>
     *
     * <p>The process engine will use this ElResolver whenever it is executing a
     * process in the context of this process application.</p>
     *
     * <p>The process engine must only call this method from Callable implementations passed
     * to {@link #execute(Callable)}</p>
     */
    public function getElResolver(): ?ELResolver;

    /**
     * <p>Returns an instance of BeanELResolver that a process application caches.</p>
     * <p>Has to be managed by the process application since BeanELResolver keeps
     * hard references to classes in a cache.</p>
     */
    public function getObjectElResolver(): BeanELResolver;

    /**
     * <p>Override this method in order to programmatically add resources to the
     * deployment created by this process application.</p>
     *
     * <p>This method is invoked at deployment time once for each process archive
     * deployed by this process application.</p>
     *
     * <p><strong>NOTE:</strong> this method must NOT call the DeploymentBuilder#deploy()
     * method.</p>
     *
     * @param deploymentBuilder the DeploymentBuilder used to construct the deployment.
     * @param processArchiveName the name of the processArchive which is currently being deployed.
     */
    public function createDeployment(string $processArchiveName, DeploymentBuilderInterface $deploymentBuilder): void;


    /**
     * <p>Allows the process application to provide an ExecutionListener which is notified about
     * all execution events in all of the process instances deployed by this process application.</p>
     *
     * <p>If this method returns 'null', the process application is not notified about execution events.</p>
     *
     * @return an ExecutionListener or null.
     */
    public function getExecutionListener(): ?ExecutionListenerInterface;

    /**
     * <p>Allows the process application to provide a TaskListener which is notified about
     * all Task events in all of the process instances deployed by this process application.</p>
     *
     * <p>If this method returns 'null', the process application is not notified about Task events.</p>
     *
     * @return a TaskListener or null.
     */
    public function getTaskListener(): ?TaskListenerInterface;
}
