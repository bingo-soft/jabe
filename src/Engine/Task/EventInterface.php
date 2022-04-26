<?php

namespace Jabe\Engine\Task;

use Jabe\Engine\History\UserOperationLogEntryInterface;

interface EventInterface
{
    public const ACTION_ADD_USER_LINK = UserOperationLogEntryInterface::OPERATION_TYPE_ADD_USER_LINK;

    public const ACTION_DELETE_USER_LINK = UserOperationLogEntryInterface::OPERATION_TYPE_DELETE_USER_LINK;

    public const ACTION_ADD_GROUP_LINK = UserOperationLogEntryInterface::OPERATION_TYPE_ADD_GROUP_LINK;

    public const ACTION_DELETE_GROUP_LINK = UserOperationLogEntryInterface::OPERATION_TYPE_DELETE_GROUP_LINK;

    public const ACTION_ADD_COMMENT = "AddComment";

    public const ACTION_ADD_ATTACHMENT = UserOperationLogEntryInterface::OPERATION_TYPE_ADD_ATTACHMENT;

    public const ACTION_DELETE_ATTACHMENT = UserOperationLogEntryInterface::OPERATION_TYPE_DELETE_ATTACHMENT;

    /** Indicates the type of of action and also indicates the meaning of the parts as exposed in {@link #getMessageParts()}  */
    public function getAction(): string;

    /** The meaning of the message parts is defined by the action as you can find in {@link #getAction()} */
    public function getMessageParts(): array;

    /** The message that can be used in case this action only has a single message part. */
    public function getMessage(): string;

    /** reference to the user that made the comment */
    public function getUserId(): string;

    /** time and date when the user made the comment */
    public function getTime(): string;

    /** reference to the task on which this comment was made */
    public function getTaskId(): string;

    /** reference to the process instance on which this comment was made */
    public function getProcessInstanceId(): string;
}
