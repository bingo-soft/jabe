<?php

namespace BpmPlatform\Model\Xml\TestModel\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Impl\Instance\ModelElementInstanceImpl;
use BpmPlatform\Model\Xml\TestModel\{
    Gender,
    TestModelConstants
};
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;

abstract class Animal extends ModelElementInstanceImpl
{
    protected static $idAttr;
    protected static $nameAttr;
    protected static $fatherRef;
    protected static $motherRef;
    protected static $isEndangeredAttr;
    protected static $genderAttr;
    protected static $ageAttr;
    protected static $bestFriendsRefCollection;
    protected static $relationshipDefinitionsColl;
    protected static $relationshipDefinitionRefsColl;

    public static function registerType(ModelBuilder $modelBuilder): void
    {

        $typeBuilder = $modelBuilder->defineType(Animal::class, TestModelConstants::TYPE_NAME_ANIMAL)
            ->namespaceUri(TestModelConstants::MODEL_NAMESPACE)
            ->abstractType();

        self::$idAttr = $typeBuilder->stringAttribute(TestModelConstants::ATTRIBUTE_NAME_ID)
            ->idAttribute()
            ->build();

        self::$nameAttr = $typeBuilder->stringAttribute(TestModelConstants::ATTRIBUTE_NAME_NAME)
            ->build();

        self::$fatherRef = $typeBuilder->stringAttribute(TestModelConstants::ATTRIBUTE_NAME_FATHER)
            ->qNameAttributeReference(Animal::class)
            ->build();

        self::$motherRef = $typeBuilder->stringAttribute(TestModelConstants::ATTRIBUTE_NAME_MOTHER)
            ->idAttributeReference(Animal::class)
            ->build();

        self::$isEndangeredAttr = $typeBuilder->booleanAttribute(TestModelConstants::ATTRIBUTE_NAME_IS_ENDANGERED)
            ->defaultValue(false)
            ->build();

        self::$genderAttr = $typeBuilder->enumAttribute(TestModelConstants::ATTRIBUTE_NAME_GENDER, Gender::class)
            ->required()
            ->build();

        self::$ageAttr = $typeBuilder->integerAttribute(TestModelConstants::ATTRIBUTE_NAME_AGE)
            ->build();

        self::$bestFriendsRefCollection = $typeBuilder->stringAttribute(
            TestModelConstants::ATTRIBUTE_NAME_BEST_FRIEND_REFS
        )
            ->idAttributeReferenceCollection(Animal::class, AnimalAttributeReferenceCollection::class)
            ->build();

        $sequence = $typeBuilder->sequence();

        self::$relationshipDefinitionsColl = $sequence->elementCollection(RelationshipDefinition::class)
            ->build();

        self::$relationshipDefinitionRefsColl = $sequence->elementCollection(RelationshipDefinitionRef::class)
            ->qNameElementReferenceCollection(RelationshipDefinition::class)
            ->build();

        $typeBuilder->build();
    }

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public function getId(): ?string
    {
        return self::$idAttr->getValue($this);
    }

    public function setId(string $id): void
    {
        self::$idAttr->setValue($this, $id);
    }

    public function getName(): ?string
    {
        return self::$nameAttr->getValue($this);
    }

    public function setName(string $name): void
    {
        self::$nameAttr->setValue($this, $name);
    }

    public function getFather(): ?Animal
    {
        return self::$fatherRef->getReferenceTargetElement($this);
    }

    public function getFatherRef()
    {
        return self::$fatherRef;
    }

    public function setFather(Animal $father): void
    {
        self::$fatherRef->setReferenceTargetElement($this, $father);
    }

    public function getMother(): ?Animal
    {
        return self::$motherRef->getReferenceTargetElement($this);
    }

    public function setMother(Animal $mother): void
    {
        self::$motherRef->setReferenceTargetElement($this, $mother);
    }

    public function isEndangered(): bool
    {
        return self::$isEndangeredAttr->getValue($this);
    }

    /**
     * @param mixed $isEndangered
     */
    public function setIsEndangered($isEndangered): void
    {
        self::$isEndangeredAttr->setValue($this, $isEndangered);
    }

    public function getGender(): ?string
    {
        return self::$genderAttr->getValue($this);
    }

    public function setGender(string $gender): void
    {
        self::$genderAttr->setValue($this, $gender);
    }

    public function getAge(): ?int
    {
        return self::$ageAttr->getValue($this);
    }

    public function setAge(int $age): void
    {
        self::$ageAttr->setValue($this, $age);
    }

    public function getRelationshipDefinition() {
        return self::$relationshipDefinitionsColl;
    }

    public function getRelationshipDefinitions(): array
    {
        return self::$relationshipDefinitionsColl->get($this);
    }

    public function getRelationshipDefinitionRefs(): array
    {
        return self::$relationshipDefinitionRefsColl->getReferenceTargetElements($this);
    }

    public function getRelationshipDefinitionRefElements(): array
    {
        return self::$relationshipDefinitionRefsColl->getReferenceSourceCollection()->get($this);
    }

    public function addRelationshipDefinitionRefElement(RelationshipDefinitionRef $rel): void
    {
        self::$relationshipDefinitionRefsColl->getReferenceSourceCollection()->add($this, $rel);
    }

    public function removeRelationshipDefinitionRefElement(RelationshipDefinitionRef $rel): void
    {
        self::$relationshipDefinitionRefsColl->getReferenceSourceCollection()->remove($this, $rel);
    }

    public function clearRelationshipDefinitionRefElements(): void
    {
        self::$relationshipDefinitionRefsColl->getReferenceSourceCollection()->clear($this);
    }

    public function getBestFriends(): array
    {
        return self::$bestFriendsRefCollection->getReferenceTargetElements($this);
    }

    public function addRelationshipDefinition(RelationshipDefinition $rel): void
    {
        self::$relationshipDefinitionRefsColl->add($this, $rel);
    }

    public function addRelationship(RelationshipDefinition $rel): void
    {
        self::$relationshipDefinitionsColl->add($this, $rel);
    }

    public function removeRelationship(RelationshipDefinition $rel): void
    {
        self::$relationshipDefinitionsColl->remove($this, $rel);
    }

    public function clearRelationships(): void
    {
        self::$relationshipDefinitionsColl->clear($this);
    }

    public function clearRelationshipDefinitions(): void
    {
        self::$relationshipDefinitionRefsColl->clear($this);
    }

    public function addFriend(Animal $friend): void
    {
        self::$bestFriendsRefCollection->add($this, $friend);
    }

    public function removeFriend(Animal $friend): void
    {
        self::$bestFriendsRefCollection->remove($this, $friend);
    }

    public function clearFriends(): void
    {
        self::$bestFriendsRefCollection->clear($this);
    }
}
