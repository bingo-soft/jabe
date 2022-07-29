<?php

namespace Jabe\Engine\Impl\Core\Model;

use Jabe\Engine\ProcessEngineException;

class Properties implements \Serializable
{
    protected $properties = [];

    public function __construct(array $properties = [])
    {
        $this->properties = $properties;
    }

    /**
     * Returns the value to which the specified property key is mapped, or
     * <code>null</code> if this properties contains no mapping for the property key.
     *
     * @param property
     *          the property key whose associated value is to be returned
     * @return mixed the value to which the specified property key is mapped, or
     *         <code>null</code> if this properties contains no mapping for the property key
     */
    public function get($property)
    {
        if ($this->contains($property)) {
            return $this->properties[$property->getName()];
        }
        if ($property instanceof PropertyKey) {
            return null;
        }
        return [];
    }

    public function sort(PropertyListKey $property, $callback): void
    {
        if ($this->contains($property)) {
            usort($this->properties[$property->getName()], $callback);
        }
    }

    public function clear($property): void
    {
        if ($this->contains($property)) {
            $this->properties[$property->getName()] = [];
        }
    }

    public function add($property, $value): void
    {
        if ($this->contains($property)) {
            $this->properties[$property->getName()][] = $value;
        } else {
            $this->properties[$property->getName()] = [ $value ];
        }
    }

    /**
     * Associates the specified value with the specified property key.
     * If the properties previously contained a mapping for the property key, the old
     * value is replaced by the specified value.
     *
     * @param <T>
     *          the type of the value
     * @param property
     *          the property key with which the specified value is to be associated
     * @param value
     *          the value to be associated with the specified property key
     */
    public function set($property, $value): void
    {
        $this->properties[$property->getName()] = $value;
    }

    /**
     * Append the value to the list to which the specified property key is mapped. If
     * this properties contains no mapping for the property key, the value append to
     * a new list witch is associate the the specified property key.
     *
     * @param <T>
     *          the type of elements in the list
     * @param property
     *          the property key whose associated list is to be added
     * @param value
     *          the value to be appended to list
     */
    public function addListItem(PropertyListKey $property, $value): void
    {
        $list = $this->get($property);
        $list[] = $value;

        if (!$this->contains($property)) {
            $this->set($property, $list);
        }
    }

    public function clearItem($property, $itemKey): void
    {
        if ($this->contains($property)) {
            $propKey = $property->getName();
            foreach ($this->properties[$propKey] as $key => $value) {
                if ($key == $itemKey) {
                    unset($this->properties[$propKey][$key]);
                    break;
                }
            }
        }
    }

    /**
     * Insert the value to the map to which the specified property key is mapped. If
     * this properties contains no mapping for the property key, the value insert to
     * a new map witch is associate the the specified property key.
     *
     * @param <K>
     *          the type of keys maintained by the map
     * @param <V>
     *          the type of mapped values
     * @param property
     *          the property key whose associated list is to be added
     * @param value
     *          the value to be appended to list
     */
    public function putMapEntry(PropertyMapKey $property, $key, $value): void
    {
        $map = $this->get($property);
        if (!$property->allowsOverwrite() && array_key_exists($key, $map)) {
            throw new ProcessEngineException("Cannot overwrite property key " . $key . ". Key already exists");
        }
        $map[$key] = $value;

        if (!$this->contains($property)) {
            $this->set($property, $map);
        }
    }

    /**
     * Returns <code>true</code> if this properties contains a mapping for the specified property key.
     *
     * @param property
     *            the property key whose presence is to be tested
     * @return bool true if this properties contains a mapping for the specified property key
     */
    public function contains($property): bool
    {
        return array_key_exists($property->getName(), $properties);
    }

    /**
     * Returns a map view of this properties. Changes to the map are not reflected
     * to the properties.
     *
     * @return a map view of this properties
     */
    public function toMap(): array
    {
        return $this->properties;
    }

    public function serialize()
    {
        return json_encode($this->properties);
    }

    public function unserialize($data)
    {
        $this->properties = json_decode($data, true);
    }

    public function __toString()
    {
        return "Properties [properties=" . json_encode($this->properties) . "]";
    }
}
