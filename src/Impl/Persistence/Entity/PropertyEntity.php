<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\Impl\ProcessEngineLogger;
use Jabe\Impl\Db\{
    EnginePersistenceLogger,
    HasDbRevisionInterface,
    DbEntityInterface
};
use Jabe\Impl\Util\ClassNameUtil;

class PropertyEntity implements DbEntityInterface, HasDbRevisionInterface
{
    //protected static final EnginePersistenceLogger LOG = ProcessEngineLogger.PERSISTENCE_LOGGER;
    private $name;
    private $revision;
    private $value;

    public function __construct(?string $name = null, ?string $value = null)
    {
        $this->name = $name;
        $this->value = $value;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getRevision(): ?int
    {
        return $this->revision;
    }

    public function setRevision(int $revision): void
    {
        $this->revision = $revision;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): void
    {
        $this->value = $value;
    }

    // persistent object methods ////////////////////////////////////////////////

    public function getId(): ?string
    {
        return $this->name;
    }

    public function getPersistentState()
    {
        return $this->value;
    }

    public function setId(?string $id): void
    {
        //throw LOG.notAllowedIdException(id);
        throw new \Exception("notAllowedId");
    }

    public function getRevisionNext(): int
    {
        return $this->revision + 1;
    }

    public function __serialize(): array
    {
        return [
            'name' => $this->name,
            'revision' => $this->revision,
            'value' => $this->value
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->name = $data['name'];
        $this->revision = $data['revision'];
        $this->value = $data['value'];
    }

    public function __toString()
    {
        $className = ClassNameUtil::getClassNameWithoutPackage(get_class($this));
        return $className
            . "[name=" . $this->name
            . ", revision=" . $this->revision
            . ", value=" . $this->value
            . "]";
    }
}
