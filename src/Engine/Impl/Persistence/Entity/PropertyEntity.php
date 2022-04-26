<?php

namespace Jabe\Engine\Impl\Persistence\Entity;

use Jabe\Engine\Impl\ProcessEngineLogger;
use Jabe\Engine\Impl\Db\{
    EnginePersistenceLogger,
    HasDbRevisionInterface,
    DbEntityInterface
};
use Jabe\Engine\Impl\Util\ClassNameUtil;

class PropertyEntity implements DbEntityInterface, HasDbRevisionInterface, \Serializable
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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getRevision(): int
    {
        return $this->revision;
    }

    public function setRevision(int $revision): void
    {
        $this->revision = $revision;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): void
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

    public function setId(string $id): void
    {
        //throw LOG.notAllowedIdException(id);
        throw new \Exception("notAllowedId");
    }

    public function getRevisionNext(): int
    {
        return $this->revision + 1;
    }

    public function serialize()
    {
        return json_encode([
            'name' => $this->name,
            'revision' => $this->revision,
            'value' => $this->value
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->name = $json->name;
        $this->revision = $json->revision;
        $this->value = $json->value;
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
