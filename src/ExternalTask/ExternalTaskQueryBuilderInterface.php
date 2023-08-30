<?php

namespace Jabe\ExternalTask;

interface ExternalTaskQueryBuilderInterface
{
    /**
     * Specifies that tasks of a topic should be fetched and locked for
     * a certain amount of time
     *
     * @param topicName the name of the topic
     * @param lockDuration the duration in milliseconds for which tasks should be locked;
     *   begins at the time of fetching
     * @return
     */
    public function topic(?string $topicName, int $lockDuration): ExternalTaskQueryTopicBuilderInterface;

    /**
     * Performs the fetching. Locks candidate tasks of the given topics
     * for the specified duration.
     *
     * @return fetched external tasks that match the topic and that can be
     *   successfully locked
     */
    public function execute(): array;
}
