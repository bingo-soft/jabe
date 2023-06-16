<?php

namespace Jabe\Impl\Pvm;

use Jabe\Impl\Core\Model\Properties;

interface PvmProcessElementInterface
{
    /**
     * The id of the element
     * @return string the id
     */
    public function getId(): ?string;

    /**
     * The process definition scope, root of the scope hierarchy.
     * @return
     */
    public function getProcessDefinition(): PvmProcessDefinitionInterface;

    public function getProperty(?string $name);

    /**
     * Returns the properties of the element.
     *
     * @return Properties the properties
     */
    public function getProperties(): Properties;
}
