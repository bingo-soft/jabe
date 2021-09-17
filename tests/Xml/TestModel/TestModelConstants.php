<?php

namespace Tests\Xml\TestModel;

class TestModelConstants
{
    public const MODEL_NAME = "animals";
    public const MODEL_NAMESPACE = "http://test.org/animals";
    public const NEWER_NAMESPACE = "http://test.org/electronic";

    public const TYPE_NAME_ANIMAL = "animal";
    public const TYPE_NAME_FLYING_ANIMAL = "flyingAnimal";
    public const TYPE_NAME_CHILD_RELATIONSHIP_DEFINITION = "childRelationshipDefinition";
    public const TYPE_NAME_FRIEND_RELATIONSHIP_DEFINITION = "friendRelationshipDefinition";
    public const TYPE_NAME_RELATIONSHIP_DEFINITION = "relationshipDefinition";
    public const TYPE_NAME_WINGS = "wings";

    public const ELEMENT_NAME_ANIMALS = "animals";
    public const ELEMENT_NAME_BIRD = "bird";
    public const ELEMENT_NAME_RELATIONSHIP_DEFINITION_REF = "relationshipDefinitionRef";
    public const ELEMENT_NAME_FLIGHT_PARTNER_REF = "flightPartnerRef";
    public const ELEMENT_NAME_FLIGHT_INSTRUCTOR = "flightInstructor";
    public const ELEMENT_NAME_SPOUSE_REF = "spouseRef";
    public const ELEMENT_NAME_EGG = "egg";
    public const ELEMENT_NAME_ANIMAL_REFERENCE = "animalReference";
    public const ELEMENT_NAME_GUARDIAN = "guardian";
    public const ELEMENT_NAME_MOTHER = "mother";
    public const ELEMENT_NAME_GUARD_EGG = "guardEgg";
    public const ELEMENT_NAME_DESCRIPTION = "description";

    public const ATTRIBUTE_NAME_ID = "id";
    public const ATTRIBUTE_NAME_NAME = "name";
    public const ATTRIBUTE_NAME_FATHER = "father";
    public const ATTRIBUTE_NAME_MOTHER = "mother";
    public const ATTRIBUTE_NAME_IS_ENDANGERED = "isEndangered";
    public const ATTRIBUTE_NAME_GENDER = "gender";
    public const ATTRIBUTE_NAME_AGE = "age";
    public const ATTRIBUTE_NAME_BEST_FRIEND_REFS = "bestFriendRefs";
    public const ATTRIBUTE_NAME_ANIMAL_REF = "animalRef";
    public const ATTRIBUTE_NAME_WINGSPAN = "wingspan";
}
