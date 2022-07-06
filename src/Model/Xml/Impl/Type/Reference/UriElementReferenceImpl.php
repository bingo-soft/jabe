<?php

namespace Jabe\Model\Xml\Impl\Type\Reference;

use Jabe\Model\Xml\Type\Child\ChildElementInterface;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;

class UriElementReferenceImpl extends ElementReferenceImpl
{
    public function __construct(ChildElementInterface $referenceSourceCollection)
    {
        parent::__construct($referenceSourceCollection);
    }

    /**
     * @return mixed
     */
    public function getReferenceIdentifier(ModelElementInstanceInterface $referenceSourceElement)
    {
        $identifier = $referenceSourceElement->getAttributeValue("href");
        if ($identifier !== null) {
            $parts = explode('#', $identifier);
            if (count($parts) > 1) {
                return $parts[count($parts) - 1];
            } else {
                return $parts[0];
            }
        } else {
            return null;
        }
    }

    protected function setReferenceIdentifier(
        ModelElementInstanceInterface $referenceSourceElement,
        string $referenceIdentifier
    ): void {
        $referenceSourceElement->setAttributeValue("href", "#" . $referenceIdentifier);
    }
}
