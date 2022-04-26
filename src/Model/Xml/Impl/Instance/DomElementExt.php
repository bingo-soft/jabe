<?php

namespace Jabe\Model\Xml\Impl\Instance;

class DomElementExt extends \DOMElement
{
    private $userData = [];

    /**
     * @param mixed $key
     * @param mixed $data
     * @param mixed $handler
     */
    public function setUserData($key, $data, $handler): void
    {
        $this->userData[$key] = $data;
    }

    /**
     * @param mixed $key
     *
     * @return mixed
     */
    public function getUserData($key)
    {
        if (array_key_exists($key, $this->userData)) {
            return $this->userData[$key];
        }
        return null;
    }
}
