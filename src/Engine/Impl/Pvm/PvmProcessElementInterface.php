<?php

namespace Jabe\Engine\Impl\Pvm;

use Jabe\Engine\Impl\Core\Model\Properties;

interface PvmProcessElementInterface extends \Serializable
{
    /**
     * The id of the element
     * @return the id
     */
    public function getId(): string;

    /**
     * The process definition scope, root of the scope hierarchy.
     * @return
     */
    public function getProcessDefinition(): PvmProcessDefinitionInterface;

    public function getProperty(string $name);

    /**
     * Returns the properties of the element.
     *
     * @return the properties
     */
    public function getProperties(): Properties;
}
