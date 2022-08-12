<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\Impl\Db\{
    DbEntityInterface,
    HistoricEntityInterface
};
use Jabe\Task\{
    CommentInterface,
    EventInterface
};
use Jabe\Impl\Util\ClassNameUtil;

class CommentEntity implements CommentInterface, EventInterface, DbEntityInterface, HistoricEntityInterface, \Serializable
{
    public const TYPE_EVENT = "event";
    public const TYPE_COMMENT = "comment";

    protected $id;

    // If comments would be removeable, revision needs to be added!

    protected $type;
    protected $userId;
    protected $time;
    protected $taskId;
    protected $processInstanceId;
    protected $action;
    protected $message;
    protected $fullMessage;
    protected $tenantId;
    protected $rootProcessInstanceId;
    protected $removalTime;

    public function serialize()
    {
        return json_encode([
            'id' => $this->id,
            'type' => $this->type,
            'userId' => $this->userId,
            'time' => $this->time,
            'taskId' => $this->taskId,
            'processInstanceId' => $this->processInstanceId,
            'action' => $this->action,
            'message' => $this->message,
            'fullMessage' => $this->fullMessage,
            'tenantId' => $this->tenantId,
            'rootProcessInstanceId' => $this->rootProcessInstanceId,
            'removalTime' => $this->removalTime
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->id = $json->id;
        $this->type = $json->type;
        $this->userId = $json->userId;
        $this->time = $json->time;
        $this->taskId = $json->taskId;
        $this->processInstanceId = $json->processInstanceId;
        $this->action = $json->action;
        $this->message = $json->message;
        $this->fullMessage = $json->fullMessage;
        $this->tenantId = $json->tenantId;
        $this->rootProcessInstanceId = $json->rootProcessInstanceId;
        $this->removalTime = $json->removalTime;
    }

    public function getPersistentState()
    {
        return CommentEntity::class;
    }

    public function getFullMessageBytes(): ?string
    {
        return $this->fullMessage;
    }

    public function setFullMessageBytes(string $fullMessageBytes): void
    {
        $this->fullMessage = $fullMessageBytes;
    }

    public const MESSAGE_PARTS_MARKER = "_|_";

    public function setMessage($messageParts): void
    {
        if (is_array($messageParts)) {
            $stringBuilder = "";
            foreach ($messageParts as $part) {
                if ($part !== null) {
                    $stringBuilder .= str_replace(self::MESSAGE_PARTS_MARKER, " | ", $part);
                    $stringBuilder .= self::MESSAGE_PARTS_MARKER;
                } else {
                    $stringBuilder .= "null";
                    $stringBuilder .= self::MESSAGE_PARTS_MARKER;
                }
            }
            for ($i = 0; $i < strlen(self::MESSAGE_PARTS_MARKER); $i += 1) {
                $stringBuilder = substr_replace($stringBuilder, '', strlen($stringBuilder) - 1, 1);
            }
            $this->message = $stringBuilder;
        } elseif (is_string($messageParts)) {
            $this->message = $messageParts;
        }
    }

    public function getMessageParts(): array
    {
        if (empty($this->message)) {
            return [];
        }
        $messageParts = [];
        $tokens = explode(self::MESSAGE_PARTS_MARKER, $this->message);
        foreach ($tokens as $nextToken) {
            $nextToken = trim($nextToken);
            if ($nextToken == "null") {
                $messageParts[] = null;
            } else {
                $messageParts[] = $nextToken;
            }
        }
        return $messageParts;
    }

    // getters and setters //////////////////////////////////////////////////////

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): void
    {
        $this->userId = $userId;
    }

    public function getTaskId(): string
    {
        return $this->taskId;
    }

    public function setTaskId(string $taskId): void
    {
        $this->taskId = $taskId;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getTime(): string
    {
        return $this->time;
    }

    public function setTime(string $time): void
    {
        $this->time = $time;
    }

    public function getProcessInstanceId(): string
    {
        return $this->processInstanceId;
    }

    public function setProcessInstanceId(string $processInstanceId): void
    {
        $this->processInstanceId = $processInstanceId;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getFullMessage(): string
    {
        return $this->fullMessage;
    }

    public function setFullMessage(string $fullMessage): void
    {
        $this->fullMessage = $fullMessage;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function setAction(string $action): void
    {
        $this->action = $action;
    }

    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }

    public function setTenantId(?string $tenantId): void
    {
        $this->tenantId = $tenantId;
    }

    public function getRootProcessInstanceId(): string
    {
        return $this->rootProcessInstanceId;
    }

    public function setRootProcessInstanceId(string $rootProcessInstanceId): void
    {
        $this->rootProcessInstanceId = $rootProcessInstanceId;
    }

    public function getRemovalTime(): string
    {
        return $this->removalTime;
    }

    public function setRemovalTime(string $removalTime): void
    {
        $this->removalTime = $removalTime;
    }

    public function __toString()
    {
        $className = ClassNameUtil::getClassNameWithoutPackage(get_class($this));
        return $className
                . "[id=" . $this->id
                . ", type=" . $this->type
                . ", userId=" . $this->userId
                . ", time=" . $this->time
                . ", taskId=" . $this->taskId
                . ", processInstanceId=" . $this->processInstanceId
                . ", rootProcessInstanceId=" . $this->rootProcessInstanceId
                . ", removalTime=" . $this->removalTime
                . ", action=" . $this->action
                . ", message=" . $this->message
                . ", fullMessage=" . $this->fullMessage
                . ", tenantId=" . $this->tenantId
                . "]";
    }
}
