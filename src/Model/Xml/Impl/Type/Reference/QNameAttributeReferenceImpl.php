<?php

namespace BpmPlatform\Model\Xml\Impl\Type\Reference;

use BpmPlatform\Model\Xml\Impl\Type\Attribute\AttributeImpl;
use BpmPlatform\Model\Xml\Impl\Util\QName;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;

class QNameAttributeReferenceImpl extends AttributeReferenceImpl
{
    public function __construct(AttributeImpl $referenceSourceAttribute)
    {
        parent::__construct($referenceSourceAttribute);
    }

    /**
     * @return mixed
     */
    public function getReferenceIdentifier(ModelElementInstanceInterface $referenceSourceElement)
    {
        $identifier = parent::getReferenceIdentifier($referenceSourceElement);
        if (!empty($identifier)) {
            $qName = QName::parseQName($identifier);
            return $qName->getLocalName();
        } else {
            return null;
        }
    }
}
