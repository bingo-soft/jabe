<?php

namespace Jabe\Impl\Util;

use Jabe\Impl\ProcessEngineLogger;
use Jabe\Impl\Bpmn\Parser\FieldDeclaration;
use Jabe\Impl\Context\Context;
use Jabe\Impl\Util\EnsureUtil;

class ClassDelegateUtil
{
    //private static final EngineUtilLogger LOG = ProcessEngineLogger.UTIL_LOGGER;

    public static function instantiateDelegate(?string $clazz, array $fieldDeclarations)
    {
        $artifactFactory = Context::getProcessEngineConfiguration()->getArtifactFactory();

        try {
            $object = $artifactFactory->getArtifact($clazz);

            self::applyFieldDeclaration($fieldDeclarations, $object);
            return $object;
        } catch (\Exception $e) {
            //throw LOG.exceptionWhileInstantiatingClass(className, e);
            throw new \Exception("exceptionWhileInstantiatingClass");
        }
    }

    public static function applyFieldDeclaration($fieldDeclarations, $target): void
    {
        if (is_array($fieldDeclarations)) {
            foreach ($fieldDeclarations as $declaration) {
                self::applyFieldDeclaration($declaration, $target);
            }
        } else {
            $declaration = $fieldDeclarations;
            $setterMethod = ReflectUtil::getSetter($declaration->getName(), get_class($target));

            if ($setterMethod != null) {
                try {
                    $setterMethod->invoke($target, $declaration->getValue());
                } catch (\Exception $e) {
                    //throw LOG.exceptionWhileApplyingFieldDeclatation(declaration.getName(), target.getClass().getName(), e);
                    throw new \Exception("exceptionWhileApplyingFieldDeclatation");
                }
            } else {
                $field = ReflectUtil::getField($declaration->getName(), $target);
                EnsureUtil::ensureNotNull("Field definition uses unexisting field '" . $declaration->getName() . "' on class " . get_class($target), "field", $field);
                // Check if the delegate field's type is correct
                if (!self::fieldTypeCompatible($declaration, $field)) {
                    //throw LOG.incompatibleTypeForFieldDeclaration(declaration, target, field);
                    throw new \Exception("incompatibleTypeForFieldDeclaration");
                }
                ReflectUtil::setField($field, $target, $declaration->getValue());
            }
        }
    }

    public static function fieldTypeCompatible(FieldDeclaration $declaration, \ReflectionProperty $field): bool
    {
        /*if ($declaration->getValue() !== null) {
            return field.getType().isAssignableFrom(declaration.getValue().getClass());
        } else {
            // Null can be set any field type
            return true;
        }*/
        return true;
    }
}
