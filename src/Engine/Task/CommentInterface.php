<?php

namespace BpmPlatform\Engine\Task;

interface CommentInterface
{
    /** comment id */
    public function getId(): string;

    /** reference to the user that made the comment */
    public function getUserId(): string;

    /** time and date when the user made the comment */
    public function getTime(): string;

    /** reference to the task on which this comment was made */
    public function getTaskId(): string;

    /** reference to the root process instance id of the process instance on which this comment was made */
    public function getRootProcessInstanceId(): string;

    /** reference to the process instance on which this comment was made */
    public function getProcessInstanceId(): string;

    /** the full comment message the user had related to the task and/or process instance
     * @see TaskService#getTaskComments(String) */
    public function getFullMessage(): string;

    /** The time the historic comment will be removed. */
    public function getRemovalTime(): string;
}
