<?php

namespace Jabe\Engine\Impl\Persistence\Entity\Util;

use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Persistence\Entity\{
    ByteArrayEntity,
    NameableInterface
};
use Jabe\Engine\Repository\ResourceTypeInterface;

class ByteArrayField
{
    protected $byteArrayValue;
    protected $byteArrayId;

    protected $nameProvider;
    protected $type;

    protected $rootProcessInstanceId;
    protected $removalTime;

    public function __construct(NameableInterface $nameProvider, ResourceTypeInterface $type, ?string $rootProcessInstanceId = null, ?string $removalTime = null)
    {
        $this->nameProvider = $nameProvider;
        $this->type = $type;
        $this->removalTime = $removalTime;
        $this->rootProcessInstanceId = $rootProcessInstanceId;
    }

    public function getByteArrayId(): string
    {
        return $this->byteArrayId;
    }

    public function setByteArrayId(string $byteArrayId): void
    {
        $this->byteArrayId = $byteArrayId;
        $this->byteArrayValue = null;
    }

    public function getByteArrayValue(): ?string
    {
        $this->getByteArrayEntity();
        if ($this->byteArrayValue !== null) {
            return $this->byteArrayValue->getBytes();
        } else {
            return null;
        }
    }

    protected function getByteArrayEntity(): ?ByteArrayEntity
    {
        if ($this->byteArrayValue === null && $this->byteArrayId !== null) {
            // no lazy fetching outside of command context
            if (Context::getCommandContext() !== null) {
                $this->byteArrayValue = Context::getCommandContext()
                    ->getDbEntityManager()
                    ->selectById(ByteArrayEntity::class, $this->byteArrayId);
                return $this->byteArrayValue;
            }
        }
        return $this->byteArrayValue;
    }

    public function setByteArrayValue($bytes, ?bool $isTransient = false): void
    {
        if ($bytes instanceof ByteArrayEntity) {
            $this->byteArrayValue = $byteArrayValue;
        } else {
            if ($bytes !== null) {
                // note: there can be cases where byteArrayId is not null
                //   but the corresponding byte array entity has been removed in parallel;
                //   thus we also need to check if the actual byte array entity still exists
                if ($this->byteArrayId !== null && $this->getByteArrayEntity() !== null) {
                    $this->byteArrayValue->setBytes($bytes);
                } else {
                    $this->deleteByteArrayValue();

                    $this->byteArrayValue = new ByteArrayEntity($this->nameProvider->getName(), $bytes, $this->type, $this->rootProcessInstanceId, $this->removalTime);

                    // avoid insert of byte array value for a transient variable
                    if (!$isTransient) {
                        Context::getCommandContext()
                        ->getByteArrayManager()
                        ->insertByteArray($this->byteArrayValue);
                        $this->byteArrayId = $this->byteArrayValue->getId();
                    }
                }
            } else {
                $this->deleteByteArrayValue();
            }
        }
    }

    public function deleteByteArrayValue(): void
    {
        if ($this->byteArrayId !== null) {
            // the next apparently useless line is probably to ensure consistency in the DbSqlSession cache,
            // but should be checked and docked here (or removed if it turns out to be unnecessary)
            $this->getByteArrayEntity();

            if ($this->byteArrayValue !== null) {
                Context::getCommandContext()
                        ->getDbEntityManager()
                        ->delete($byteArrayValue);
            }

            $this->byteArrayId = null;
        }
    }

    public function setRootProcessInstanceId(string $rootProcessInstanceId): void
    {
        $this->rootProcessInstanceId = $rootProcessInstanceId;
    }

    public function setRemovalTime(string $removalTime): void
    {
        $this->removalTime = $removalTime;
    }
}
