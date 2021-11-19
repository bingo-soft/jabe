<?php

namespace BpmPlatform\Engine\Impl\Core\Model;

use BpmPlatform\Engine\Delegate\{
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

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @see Properties#set(PropertyKey, Object)
     */
    public function setProperty(string $name, $value): void
    {
        $this->properties->set(new PropertyKey($name), $value);
    }

    /**
     * @see Properties#get(PropertyKey)
     */
    public function getProperty(string $name)
    {
        return $this->properties->get(new PropertyKey($name));
    }

    /**
     * Returns the properties of the element.
     *
     * @return the properties
     */
    public function getProperties(): Properties
    {
        return $this->properties;
    }

    public function setProperties(Properties $properties): void
    {
        $this->properties = $properties;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getListeners(?string $eventName = null): array
    {
        $listeners = $this->listeners;
        if ($eventName == null) {
            return $listeners;
        }
        if (array_key_exists($eventName, $listeners)) {
            return $listeners[$eventName];
        }
        return [];
    }

    public function getBuiltInListeners(?string $eventName = null): array
    {
        $listeners = $this->builtInListeners;
        if ($eventName == null) {
            return $listeners;
        }
        if (array_key_exists($eventName, $listeners)) {
            return $listeners[$eventName];
        }
        return [];
    }

    public function getVariableListenersLocal(?string $eventName = null): array
    {
        $listeners = $this->variableListeners;
        if ($eventName == null) {
            return $listeners;
        }
        if (array_key_exists($eventName, $listeners)) {
            return $listeners[$eventName];
        }
        return [];
    }

    public function getBuiltInVariableListenersLocal(?string $eventName = null): array
    {
        $listeners = $this->builtInVariableListeners;
        if ($eventName == null) {
            return $listeners;
        }
        if (array_key_exists($eventName, $listeners)) {
            return $listeners[$eventName];
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

    public function addListener(string $eventName, DelegateListenerInterface $listener, ?int $index = -1): void
    {
        $this->addListenerToMap($this->listeners, $eventName, $listener, $index);
    }

    public function addBuiltInListener(string $eventName, DelegateListenerInterface $listener, ?int $index = -1): void
    {
        $this->addListenerToMap($this->listeners, $eventName, $listener, $index);
        $this->addListenerToMap($this->builtInListeners, $eventName, $listener, $index);
    }

    public function addBuiltInVariableListener(string $eventName, VariableListenerInterface $listener, ?int $index = -1): void
    {
        $this->addListenerToMap($this->variableListeners, $eventName, $listener, $index);
        $this->addListenerToMap($this->builtInVariableListeners, $eventName, $listener, $index);
    }

    protected function addListenerToMap(array &$listenerMap, string $eventName, DelegateListenerInterface $listener, int $index): void
    {
        if (!array_key_exists($eventName, $listenerMap)) {
            $listenerMap[$eventName] = [];
        }
        if ($index < 0) {
            $listenerMap[$eventName][] = $listener;
        } else {
            $listenerMap[$eventName][$index] = $listener;
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
