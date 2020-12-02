<?php

namespace BpmPlatform\Model\Xml\Type\Child;

use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;

interface ChildElementInterface
{
    /**
     * @param ModelElementInstanceInterface $element
     * @param mixed $newChildElement
     */
    public function setChild(ModelElementInstanceInterface $element, $newChildElement): void;

    /**
     * @param ModelElementInstanceInterface $element
     *
     * @return mixed
     */
    public function getChild(ModelElementInstanceInterface $element);

    public function removeChild(ModelElementInstanceInterface $element): bool;
}
