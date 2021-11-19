<?php

namespace BpmPlatform\Engine\Runtime;

interface EventSubscriptionInterface
{
    /**
     * The unique identifier of the event subscription.
     */
    public function getId(): string;

    /**
     * The event subscriptions type. "message" identifies message event subscriptions,
     * "signal" identifies signal event subscription, "compensation" identifies event subscriptions
     * used for compensation events.
     */
    public function getEventType(): string;

    /**
     * The name of the event this subscription belongs to as defined in the process model.
     */
    public function getEventName(): string;

    /**
     * The execution that is subscribed on the referenced event.
     */
    public function getExecutionId(): string;

    /**
     * The process instance this subscription belongs to.
     */
    public function getProcessInstanceId(): string;

    /**
     * The identifier of the activity that this event subscription belongs to.
     * This could for example be the id of a receive task.
     */
    public function getActivityId(): string;

    /**
     * The id of the tenant this event subscription belongs to. Can be <code>null</code>
     * if the subscription belongs to no single tenant.
     */
    public function getTenantId(): ?string;

    /**
     * The time this event subscription was created.
     */
    public function getCreated(): string;
}
