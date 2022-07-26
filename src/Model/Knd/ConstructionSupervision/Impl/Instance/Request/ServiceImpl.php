<?php

namespace Jabe\Model\Knd\ConstructionSupervision\Impl\Instance\Request;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Impl\Instance\ModelElementInstanceImpl;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Knd\ConstructionSupervision\Impl\RequestModelConstants;
use Jabe\Model\Knd\ConstructionSupervision\Instance\Request\{
    CurrentDateInterface,
    OrderIdInterface,
    OrderStatusCodeInterface,
    ServiceInterface,
    TargetIdInterface,
    TargetNameInterface,
    DepartmentIdInterface,
    DepartmentNameInterface,
    OkatoInterface,
    UserTypeInterface
};

class ServiceImpl extends ModelElementInstanceImpl implements ServiceInterface
{
    private static $currentDate;
    private static $orderId;
    private static $orderStatusCode;
    private static $targetId;
    private static $targetName;
    private static $departmentId;
    private static $departmentName;
    private static $okato;
    private static $userType;

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            ServiceInterface::class,
            RequestModelConstants::ELEMENT_NAME_SERVICE
        )
        ->namespaceUri(RequestModelConstants::MODEL_NAMESPACE)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ServiceInterface
                {
                    return new ServiceImpl($instanceContext);
                }
            }
        );

        $sequenceBuilder = $typeBuilder->sequence();

        self::$currentDate = $sequenceBuilder->element(CurrentDateInterface::class)
        ->build();
        self::$orderId = $sequenceBuilder->element(OrderIdInterface::class)
        ->build();
        self::$orderStatusCode = $sequenceBuilder->element(OrderStatusCodeInterface::class)
        ->build();
        self::$targetId = $sequenceBuilder->element(TargetIdInterface::class)
        ->build();
        self::$targetName = $sequenceBuilder->element(TargetNameInterface::class)
        ->build();
        self::$departmentId = $sequenceBuilder->element(DepartmentIdInterface::class)
        ->build();
        self::$departmentName = $sequenceBuilder->element(DepartmentNameInterface::class)
        ->build();
        self::$okato = $sequenceBuilder->element(OkatoInterface::class)
        ->build();
        self::$userType = $sequenceBuilder->element(UserTypeInterface::class)
        ->build();

        $typeBuilder->build();
    }

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public function getCurrentDate(): CurrentDateInterface
    {
        return self::$currentDate->getChild($this);
    }

    public function getOrderId(): OrderIdInterface
    {
        return self::$orderId->getChild($this);
    }

    public function getOrderStatusCode(): OrderStatusCodeInterface
    {
        return self::$orderStatusCode->getChild($this);
    }

    public function getTargetId(): TargetIdInterface
    {
        return self::$targetId->getChild($this);
    }

    public function getTargetName(): TargetNameInterface
    {
        return self::$targetName->getChild($this);
    }

    public function getDepartmentId(): DepartmentIdInterface
    {
        return self::$departmentId->getChild($this);
    }

    public function getDepartmentName(): DepartmentNameInterface
    {
        return self::$departmentName->getChild($this);
    }

    public function getOkato(): OkatoInterface
    {
        return self::$okato->getChild($this);
    }

    public function getUserType(): UserTypeInterface
    {
        return self::$userType->getChild($this);
    }

    public function asArray(): array
    {
        return [
            "currentDate" => self::$currentDate->getChild($this)->getTextContent(),
            "userType" => self::$userType->getChild($this)->getTextContent(),
            "orderId" => self::$orderId->getChild($this)->getTextContent(),
            "orderStatusCode" => self::$orderStatusCode->getChild($this)->getTextContent(),
            "TargetId" => self::$targetId->getChild($this)->getTextContent(),
            "TargetName" => self::$targetName->getChild($this)->getTextContent(),
            "DepartmentId" => self::$departmentId->getChild($this)->getTextContent(),
            "DepartmentName" => self::$departmentName->getChild($this)->getTextContent(),
            "okato" => self::$okato->getChild($this)->getTextContent(),
        ];
    }
}
