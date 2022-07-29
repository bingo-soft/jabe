<?php

namespace Jabe\Engine\Impl\Util;

use Jabe\Engine\Delegate\ExpressionInterface;
use Jabe\Engine\Exception\NotValidException;
use Jabe\Engine\Impl\Cfg\ProcessEngineConfigurationImpl;
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\El\ExpressionManager;
use Jabe\Engine\Impl\Scripting\{
    ExecutableScript,
    ScriptFactory
};
use Jabe\Engine\Impl\Scripting\Engine\JuelScriptEngineFactory;
use Jabe\Engine\Impl\Util\EnsureUtil;

class ScriptUtil
{
    /**
     * Creates a new {@link ExecutableScript} from a source or resource. It excepts static and
     * dynamic sources and resources. Dynamic means that the source or resource is an expression
     * which will be evaluated during execution.
     *
     * @param language the language of the script
     * @param source the source code of the script or an expression which evaluates to the source code
     * @param resource the resource path of the script code or an expression which evaluates to the resource path
     * @param expressionManager the expression manager to use to generate the expressions of dynamic scripts
     * @return the newly created script
     * @throws NotValidException if language is null or empty or both of source and resource are null or empty
     */
    public static function getScript(string $language, string $source, ?string $resource, ?ExpressionManager $expressionManager): ExecutableScript
    {
        $scriptFactory = self::getScriptFactory();
        EnsureUtil::ensureNotEmpty(NotValidException::class, "Script language", $language);
        EnsureUtil::ensureAtLeastOneNotNull("No script source or resource was given", $source, $resource);
        if (!empty($resource)) {
            return self::getScriptFromResource($language, $resource, $expressionManager, $scriptFactory);
        } else {
            return self::getScriptFormSource($language, $source, $expressionManager, $scriptFactory);
        }
    }

    /**
     * Creates a new {@link ExecutableScript} from a source. It excepts static and dynamic sources.
     * Dynamic means that the source is an expression which will be evaluated during execution.
     *
     * @param language the language of the script
     * @param source the source code of the script or an expression which evaluates to the source code
     * @param expressionManager the expression manager to use to generate the expressions of dynamic scripts
     * @param scriptFactory the script factory used to create the script
     * @return the newly created script
     * @throws NotValidException if language is null or empty or source is null
     */
    public static function getScriptFromSource(string $language, string $source, ?ExpressionManager $expressionManager, ScriptFactory $scriptFactory): ExecutableScript
    {
        EnsureUtil::ensureNotEmpty(NotValidException::class, "Script language", $language);
        EnsureUtil::ensureNotNull(NotValidException::class, "Script source", $source);
        if (self::isDynamicScriptExpression($language, $source)) {
            $sourceExpression = $expressionManager->createExpression($source);
            return self::getScriptFromSourceExpression($language, $sourceExpression, $scriptFactory);
        } else {
            EnsureUtil::ensureNotEmpty(NotValidException::class, "Script language", $language);
            EnsureUtil::ensureNotNull(NotValidException::class, "Script source", $source);
            return $scriptFactory->createScriptFromSource($language, $source);
        }
    }

    /**
     * Creates a new {@link ExecutableScript} from a dynamic source. Dynamic means that the source
     * is an expression which will be evaluated during execution.
     *
     * @param language the language of the script
     * @param sourceExpression the expression which evaluates to the source code
     * @param scriptFactory the script factory used to create the script
     * @return the newly created script
     * @throws NotValidException if language is null or empty or sourceExpression is null
     */
    public static function getScriptFromSourceExpression(string $language, ExpressionInterface $sourceExpression, ScriptFactory $scriptFactory): ExecutableScript
    {
        EnsureUtil::ensureNotEmpty(NotValidException::class, "Script language", $language);
        EnsureUtil::ensureNotNull(NotValidException::class, "Script source expression", $sourceExpression);
        return $scriptFactory->createScriptFromSource($language, $sourceExpression);
    }

    /**
     * Creates a new {@link ExecutableScript} from a resource. It excepts static and dynamic resources.
     * Dynamic means that the resource is an expression which will be evaluated during execution.
     *
     * @param language the language of the script
     * @param resource the resource path of the script code or an expression which evaluates to the resource path
     * @param expressionManager the expression manager to use to generate the expressions of dynamic scripts
     * @param scriptFactory the script factory used to create the script
     * @return the newly created script
     * @throws NotValidException if language or resource are null or empty
     */
    public static function getScriptFromResource(string $language, string $resource, ?ExpressionManager $expressionManager, ?ScriptFactory $scriptFactory): ExecutableScript
    {
        EnsureUtil::ensureNotEmpty(NotValidException::class, "Script language", $language);
        EnsureUtil::ensureNotEmpty(NotValidException::class, "Script resource", $resource);
        if (self::isDynamicScriptExpression($language, $resource)) {
            $resourceExpression = $expressionManager->createExpression($resource);
            return self::getScriptFromResourceExpression($language, $resourceExpression, $scriptFactory);
        } else {
            EnsureUtil::ensureNotEmpty(NotValidException::class, "Script language", $language);
            EnsureUtil::ensureNotEmpty(NotValidException::class, "Script resource", $resource);
            return $scriptFactory->createScriptFromResource($language, $resource);
        }
    }

    /**
     * Creates a new {@link ExecutableScript} from a dynamic resource. Dynamic means that the source
     * is an expression which will be evaluated during execution.
     *
     * @param language the language of the script
     * @param resourceExpression the expression which evaluates to the resource path
     * @param scriptFactory the script factory used to create the script
     * @return the newly created script
     * @throws NotValidException if language is null or empty or resourceExpression is null
     */
    public static function getScriptFromResourceExpression(string $language, ExpressionInterface $resourceExpression, ScriptFactory $scriptFactory): ExecutableScript
    {
        EnsureUtil::ensureNotEmpty(NotValidException::class, "Script language", $language);
        EnsureUtil::ensureNotNull(NotValidException::class, "Script resource expression", $resourceExpression);
        return $scriptFactory->createScriptFromResource($language, $resourceExpression);
    }

    /**
     * Checks if the value is an expression for a dynamic script source or resource.
     *
     * @param language the language of the script
     * @param value the value to check
     * @return true if the value is an expression for a dynamic script source/resource, otherwise false
     */
    public static function isDynamicScriptExpression(?string $language, string $value): bool
    {
        return StringUtil::isExpression($value) && $language != null && !in_array(strtolower($language), JuelScriptEngineFactory::$names);
    }

    /**
     * Returns the configured script factory in the context or a new one.
     */
    public static function getScriptFactory(): ScriptFactory
    {
        $processEngineConfiguration = Context::getProcessEngineConfiguration();
        if ($processEngineConfiguration !== null) {
            return $processEngineConfiguration->getScriptFactory();
        } else {
            return new ScriptFactory();
        }
    }
}
