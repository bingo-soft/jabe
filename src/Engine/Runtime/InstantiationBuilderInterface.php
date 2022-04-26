<?php

namespace Jabe\Engine\Runtime;

interface InstantiationBuilderInterface
{
    /**
     * <p><i>Submits the instruction:</i></p>
     *
     * <p>Start before the specified activity.</p>
     *
     * <p>In particular:
     *   <ul>
     *     <li>In the parent activity hierarchy, determine the closest existing ancestor activity instance</li>
     *     <li>Instantiate all parent activities up to the ancestor's activity</li>
     *     <li>Instantiate and execute the given activity (respects the asyncBefore
     *       attribute of the activity)</li>
     *   </ul>
     * </p>
     *
     * @param activityId the activity to instantiate
     * @throws ProcessEngineException if more than one possible ancestor activity instance exists
     */
    public function startBeforeActivity(string $activityId): InstantiationBuilderInterface;

    /**
     * Submits an instruction that behaves like startTransition and always instantiates
     * the single outgoing sequence flow of the given activity. Does not consider asyncAfter.
     *
     * @param activityId the activity for which the outgoing flow should be executed
     * @throws ProcessEngineException if the activity has 0 or more than 1 outgoing sequence flows
     */
    public function startAfterActivity(string $activityId): InstantiationBuilderInterface;

    /**
     * <p><i>Submits the instruction:</i></p>
     *
     * <p>Start a sequence flow.</p>
     *
     * <p>In particular:
     *   <ul>
     *     <li>In the parent activity hierarchy, determine the closest existing ancestor activity instance</li>
     *     <li>Instantiate all parent activities up to the ancestor's activity</li>
     *     <li>Execute the given transition (does not consider sequence flow conditions)</li>
     *   </ul>
     * </p>
     *
     * @param transitionId the sequence flow to execute
     * @throws ProcessEngineException if more than one possible ancestor activity instance exists
     */
    public function startTransition(string $transitionId): InstantiationBuilderInterface;
}
