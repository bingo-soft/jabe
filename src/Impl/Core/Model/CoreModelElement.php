<?php

namespace Jabe\Impl\Core\Model;

use Jabe\Delegate\{
    BaseDelegateExecutionInterface,
    DelegateListenerInterface,
    VariableListenerInterface
};

abstract class CoreModelElement implements \Serializable
{
    protected $id;
    protected $name;
    protected $properties;

    /** contains built-in listeners */
    protected $builtInListeners = [];

    /** contains all listeners (built-in + user-provided) */
    protected $listeners = [];

    protected $builtInVariableListeners = [];

    protected $variableListeners = [];

    public function __construct(?string $id)
    {
        $this->id = $id;
        $this->properties = new Properties();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @see Properties#set(PropertyKey, Object)
     */
    public function setProperty(?string $name, $value): void
    {
        $this->properties->set(new PropertyKey($name), $value);
    }

    /**
     * @see Properties#get(PropertyKey)
     */
    public function getProperty(?string $name)
    {
        return $this->properties->get(new PropertyKey($name));
    }

    public function clearPropertyItem(?string $name, ?string $itemKey): void
    {
        $this->properties->clearItem(new PropertyKey($name), $itemKey);
    }

    /**
     * Returns the properties of the element.
     *
     * @return Properties the properties
     */
    public function getProperties(): Properties
    {
        return $this->properties;
    }

    public function setProperties(Properties $properties): void
    {
        $this->properties = $properties;
    }

    public function clearProperty($name): void
    {
        $this->properties->clear(new PropertyKey($name));
    }

    public function sortProperties(PropertyListKey $key, $callback): void
    {
        $this->properties->sort($key, $callback);
    }

    public function addProperty($name, $value): void
    {
        $this->properties->add(new PropertyKey($name), $value);
    }

    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getListeners(?string $eventName = null): array
    {
        if ($eventName === null) {
            return $this->listeners;
        }
        if (array_key_exists($eventName, $this->listeners)) {
            return $this->listeners[$eventName];
        }
        return [];
    }

    public function getBuiltInListeners(?string $eventName = null): array
    {
        if ($eventName === null) {
            return $this->builtInListeners;
        }
        if (array_key_exists($eventName, $this->builtInListeners)) {
            return $this->builtInListeners[$eventName];
        }
        return [];
    }

    public function getVariableListenersLocal(?string $eventName = null): array
    {
        if ($eventName === null) {
            return $this->variableListeners;
        }
        if (array_key_exists($eventName, $this->variableListeners)) {
            return $this->variableListeners[$eventName];
        }
        return [];
    }

    public function getBuiltInVariableListenersLocal(?string $eventName = null): array
    {
        if ($eventName === null) {
            return $this->builtInVariableListeners;
        }
        if (array_key_exists($eventName, $this->builtInVariableListeners)) {
            return $this->builtInVariableListeners[$eventName];
        }
        return [];
    }

    public function getBuiltInVariableListeners(): array
    {
        return $this->builtInVariableListeners;
    }

    public function getVariableListeners(): array
    {
        return $this->variableListeners;
    }

    public function addListener(?string $eventName, DelegateListenerInterface $listener, ?int $index = -1): void
    {
        $this->addListenerToMap($this->listeners, $eventName, $listener, $index);
    }

    public function addBuiltInListener(?string $eventName, DelegateListenerInterface $listener, ?int $index = -1): void
    {
        $this->addListenerToMap($this->listeners, $eventName, $listener, $index);
        $this->addListenerToMap($this->builtInListeners, $eventName, $listener, $index);
    }

    public function addBuiltInVariableListener(?string $eventName, VariableListenerInterface $listener, ?int $index = -1): void
    {
        $this->addListenerToMap($this->variableListeners, $eventName, $listener, $index);
        $this->addListenerToMap($this->builtInVariableListeners, $eventName, $listener, $index);
    }

    protected function addListenerToMap(array &$listenerMap, ?string $eventName, DelegateListenerInterface $listener, int $index): void
    {
        if (!array_key_exists($eventName, $listenerMap)) {
            $listenerMap[$eventName] = [];
        }
        $listeners = &$listenerMap[$eventName];
        if ($index < 0) {
            $listeners[] = $listener;
        } else {
            array_splice($listeners, $index, 0, [ $listener ]);
        }
    }

    public function serialize()
    {
        return json_encode([
            'id' => $this->id,
            'name' => $this->name,
            'properties' => serialize($this->properties)
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->id = $json->id;
        $this->name = $json->name;
        $this->properties = unserialize($json->properties);
    }
}
