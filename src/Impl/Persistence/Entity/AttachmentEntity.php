<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\Impl\Db\{
    HasDbRevisionInterface,
    DbEntityInterface,
    HistoricEntityInterface
};
use Jabe\Task\AttachmentInterface;
use Jabe\Impl\Util\ClassNameUtil;

class AttachmentEntity implements AttachmentInterface, DbEntityInterface, HasDbRevisionInterface, HistoricEntityInterface, \Serializable
{
    protected $id;
    protected $revision;
    protected $name;
    protected $description;
    protected $type;
    protected $taskId;
    protected $processInstanceId;
    protected $url;
    protected $contentId;
    protected $content;
    protected $tenantId;
    protected $createTime;
    protected $rootProcessInstanceId;
    protected $removalTime;

    public function getPersistentState()
    {
        $persistentState = [];
        $persistentState["name"] = $this->name;
        $persistentState["description"] = $this->description;
        return $persistentState;
    }

    public function getRevisionNext(): int
    {
        return $this->revision + 1;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getRevision(): int
    {
        return $this->revision;
    }

    public function setRevision(int $revision): void
    {
        $this->revision = $revision;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getTaskId(): string
    {
        return $this->taskId;
    }

    public function setTaskId(string $taskId): void
    {
        $this->taskId = $taskId;
    }

    public function getProcessInstanceId(): string
    {
        return $this->processInstanceId;
    }

    public function setProcessInstanceId(string $processInstanceId): void
    {
        $this->processInstanceId = $processInstanceId;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function getContentId(): ?string
    {
        return $this->contentId;
    }

    public function setContentId(string $contentId): void
    {
        $this->contentId = $contentId;
    }

    public function getContent(): ByteArrayEntity
    {
        return $this->content;
    }

    public function setContent(ByteArrayEntity $content): void
    {
        $this->content = $content;
    }

    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }

    public function setTenantId(?string $tenantId): void
    {
        $this->tenantId = $tenantId;
    }

    public function getCreateTime(): string
    {
        return $this->createTime;
    }

    public function setCreateTime(string $createTime): void
    {
        $this->createTime = $createTime;
    }

    public function getRootProcessInstanceId(): string
    {
        return $this->rootProcessInstanceId;
    }

    public function getRemovalTime(): string
    {
        return $this->removalTime;
    }

    public function setRemovalTime(string $removalTime): void
    {
        $this->removalTime = $removalTime;
    }

    public function setRootProcessInstanceId(string $rootProcessInstanceId): void
    {
        $this->rootProcessInstanceId = $rootProcessInstanceId;
    }

    public function serialize()
    {
        return json_encode([
            'id' => $this->id,
            'revision' => $this->revision,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'taskId' => $this->taskId,
            'processInstanceId' => $this->processInstanceId,
            'rootProcessInstanceId' => $this->rootProcessInstanceId,
            'removalTime' => $this->removalTime,
            'url' => $this->url,
            'contentId' => $this->contentId,
            'content' => $this->content,
            'tenantId' => $this->tenantId,
            'createTime' => $this->createTime
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->id = $json->id;
        $this->revision = $json->revision;
        $this->name = $json->name;
        $this->description = $json->description;
        $this->type = $json->type;
        $this->taskId = $json->taskId;
        $this->processInstanceId = $json->processInstanceId;
        $this->rootProcessInstanceId = $json->rootProcessInstanceId;
        $this->removalTime = $json->removalTime;
        $this->url = $json->url;
        $this->contentId = $json->contentId;
        $this->content = $json->content;
        $this->tenantId = $json->tenantId;
        $this->createTime = $json->createTime;
    }

    public function __toString()
    {
        $className = ClassNameUtil::getClassNameWithoutPackage(get_class($this));
        return $className
                . "[id=" . $this->id
                . ", revision=" . $this->revision
                . ", name=" . $this->name
                . ", description=" . $this->description
                . ", type=" . $this->type
                . ", taskId=" . $this->taskId
                . ", processInstanceId=" . $this->processInstanceId
                . ", rootProcessInstanceId=" . $this->rootProcessInstanceId
                . ", removalTime=" . $this->removalTime
                . ", url=" . $this->url
                . ", contentId=" . $this->contentId
                . ", content=" . $this->content
                . ", tenantId=" . $this->tenantId
                . ", createTime=" . $this->createTime
                . "]";
    }
}
