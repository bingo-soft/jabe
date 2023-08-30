<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\Impl\Db\DbEntityInterface;
use Jabe\Impl\Metrics\Util\MetricsUtil;
use Jabe\Management\MetricIntervalValueInterface;

class MetricIntervalEntity implements MetricIntervalValueInterface, DbEntityInterface
{
    protected $timestamp;

    protected $name;

    protected $reporter;

    protected $value;

    public function __construct(?string $timestamp, ?string $name, ?string $reporter)
    {
        $this->timestamp = $timestamp;
        $this->name = $name;
        $this->reporter = $reporter;
    }

    public function __serialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'reporter' => $this->reporter,
            'value' => $this->value,
            'timestamp' => $this->timestamp
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->id = $data['id'];
        $this->name = $data['name'];
        $this->reporter = $data['reporter'];
        $this->value = $data['value'];
        $this->timestamp = $data['timestamp'];
    }

    public function getTimestamp(): ?string
    {
        return $this->timestamp;
    }

    public function setTimestamp(?string $timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    public function getName(): ?string
    {
        return MetricsUtil::resolvePublicName($this->name);
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getReporter(): ?string
    {
        return $this->reporter;
    }

    public function setReporter(?string $reporter): void
    {
        $this->reporter = $reporter;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function setValue(int $value): void
    {
        $this->value = $value;
    }

    public function getId(): ?string
    {
        return $this->name . $this->reporter . $this->timestamp;
    }

    public function setId(?string $id): void
    {
        throw new \Exception("Not supported yet.");
    }

    public function getPersistentState()
    {
        return (new \ReflectionClass($this));
    }

    public function equals($obj): bool
    {
        if ($this == $obj) {
            return true;
        }
        if ($obj === null) {
            return false;
        }
        if (get_class($this) != get_class($obj)) {
            return false;
        }
        if (($this->name === null) ? ($obj->name !== null) : $this->name != $obj->name) {
            return false;
        }
        if (($this->reporter === null) ? ($obj->reporter !== null) : $this->reporter != $obj->reporter) {
            return false;
        }
        if ($this->timestamp != $obj->timestamp && ($this->timestamp === null || $this->timestamp != $obj->timestamp)) {
            return false;
        }
        return true;
    }
}
