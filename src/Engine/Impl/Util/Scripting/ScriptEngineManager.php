<?php

namespace BpmPlatform\Engine\Impl\Util\Scripting;

class ScriptEngineManager
{
    private static $DEBUG = false;

    /**
     * This constructor loads the implementations of
     * <code>ScriptEngineFactory</code> visible to the given
     * <code>ClassLoader</code> using the <a href="../../../technotes/guides/jar/jar.html#Service%20Provider">service provider</a> mechanism.<br><br>
     * If loader is <code>null</code>, the script engine factories that are
     * bundled with the platform and that are in the usual extension
     * directories (installed extensions) are loaded. <br><br>
     *
     * @param loader ClassLoader used to discover script engine factories.
     */
    public function __construct()
    {
        $this->init();
    }

    private function init(): void
    {
        $this->globalScope = new SimpleBindings();
        $this->engineSpis = [];
        $this->nameAssociations = [];
        $this->extensionAssociations = [];
        $this->mimeTypeAssociations = [];
        //$this->initEngines();
    }

    /**
     * <code>setBindings</code> stores the specified <code>Bindings</code>
     * in the <code>globalScope</code> field. ScriptEngineManager sets this
     * <code>Bindings</code> as global bindings for <code>ScriptEngine</code>
     * objects created by it.
     *
     * @param bindings The specified <code>Bindings</code>
     * @throws IllegalArgumentException if bindings is null.
     */
    public function setBindings(BindingsInterface $bindings): void
    {
        $this->globalScope = $bindings;
    }

    /**
     * <code>getBindings</code> returns the value of the <code>globalScope</code> field.
     * ScriptEngineManager sets this <code>Bindings</code> as global bindings for
     * <code>ScriptEngine</code> objects created by it.
     *
     * @return The globalScope field.
     */
    public function getBindings(): BindingsInterface
    {
        return $this->globalScope;
    }

    /**
     * Sets the specified key/value pair in the Global Scope.
     * @param key Key to set
     * @param value Value to set.
     * @throws NullPointerException if key is null.
     * @throws IllegalArgumentException if key is empty string.
     */
    public function put(string $key, $value): void
    {
        $this->globalScope->put($key, $value);
    }

    /**
     * Gets the value for the specified key in the Global Scope
     * @param key The key whose value is to be returned.
     * @return The value for the specified key.
     */
    public function get(string $key)
    {
        return $this->globalScope->get($key);
    }

    /**
     * Looks up and creates a <code>ScriptEngine</code> for a given  name.
     * The algorithm first searches for a <code>ScriptEngineFactory</code> that has been
     * registered as a handler for the specified name using the <code>registerEngineName</code>
     * method.
     * <br><br> If one is not found, it searches the set of <code>ScriptEngineFactory</code> instances
     * stored by the constructor for one with the specified name.  If a <code>ScriptEngineFactory</code>
     * is found by either method, it is used to create instance of <code>ScriptEngine</code>.
     * @param shortName The short name of the <code>ScriptEngine</code> implementation.
     * returned by the <code>getNames</code> method of its <code>ScriptEngineFactory</code>.
     * @return A <code>ScriptEngine</code> created by the factory located in the search.  Returns null
     * if no such factory was found.  The <code>ScriptEngineManager</code> sets its own <code>globalScope</code>
     * <code>Bindings</code> as the <code>GLOBAL_SCOPE</code> <code>Bindings</code> of the newly
     * created <code>ScriptEngine</code>.
     * @throws NullPointerException if shortName is null.
     */
    public function getEngineByName(string $shortName): ?ScriptEngineInterface
    {
        if (array_key_exists($shortName, $this->nameAssociations)) {
            $obj = $this->nameAssociations[$shortName];
            $spi = $obj;
            try {
                $engine = $spi->getScriptEngine();
                $engine->setBindings($this->getBindings(), ScriptContextInterface::GLOBAL_SCOPE);
                return $engine;
            } catch (\Exception $exp) {
                if (self::$DEBUG) {
                    throw $exp;
                }
            }
        }

        foreach ($this->engineSpis as $spi) {
            $names = null;
            try {
                $names = $spi->getNames();
            } catch (\Exception $exp) {
                if (self::$DEBUG) {
                    throw $exp;
                }
            }

            if ($names != null) {
                foreach ($names as $name) {
                    if ($shortName == $name) {
                        try {
                            $engine = $spi->getScriptEngine();
                            $engine->setBindings($this->getBindings(), ScriptContextInterface::GLOBAL_SCOPE);
                            return $engine;
                        } catch (\Exception $exp) {
                            if (self::$DEBUG) {
                                throw $exp;
                            }
                        }
                    }
                }
            }
        }

        return null;
    }

    /**
     * Look up and create a <code>ScriptEngine</code> for a given extension.  The algorithm
     * used by <code>getEngineByName</code> is used except that the search starts
     * by looking for a <code>ScriptEngineFactory</code> registered to handle the
     * given extension using <code>registerEngineExtension</code>.
     * @param extension The given extension
     * @return The engine to handle scripts with this extension.  Returns <code>null</code>
     * if not found.
     * @throws NullPointerException if extension is null.
     */
    public function getEngineByExtension(string $extension): ScriptEngineInterface
    {
        if (array_key_exists($extension, $this->extensionAssociations)) {
            $obj = $this->extensionAssociations->get($extension);
            $spi = $obj;
            try {
                $engine = $spi->getScriptEngine();
                $engine->setBindings($this->getBindings(), ScriptContextInterface::GLOBAL_SCOPE);
                return $engine;
            } catch (\Exception $exp) {
                if (self::$DEBUG) {
                    throw $exp;
                }
            }
        }

        foreach ($this->engineSpis as $spi) {
            $exts = null;
            try {
                $exts = $spi->getExtensions();
            } catch (\Exception $exp) {
                if (self::$DEBUG) {
                    throw $exp;
                }
            }
            if ($exts == null) {
                continue;
            }
            foreach ($exts as $ext) {
                if ($extension == $ext) {
                    try {
                        $engine = $spi->getScriptEngine();
                        $engine->setBindings($this->getBindings(), ScriptContextInterface::GLOBAL_SCOPE);
                        return $engine;
                    } catch (\Exception $exp) {
                        if (self::$DEBUG) {
                            throw $exp;
                        }
                    }
                }
            }
        }
        return null;
    }

    /**
     * Returns a list whose elements are instances of all the <code>ScriptEngineFactory</code> classes
     * found by the discovery mechanism.
     * @return List of all discovered <code>ScriptEngineFactory</code>s.
     */
    public function getEngineFactories(): array
    {
        $res = [];
        foreach ($this->engineSpis as $spi) {
            $res[] = $spi;
        }
        return $res;
    }

    /**
     * Registers a <code>ScriptEngineFactory</code> to handle a language
     * name.  Overrides any such association found using the Discovery mechanism.
     * @param name The name to be associated with the <code>ScriptEngineFactory</code>.
     * @param factory The class to associate with the given name.
     * @throws NullPointerException if any of the parameters is null.
     */
    public function registerEngineName(string $name, ScriptEngineFactoryInterface $factory): void
    {
        $this->nameAssociations[$name] = $factory;
    }

    /**
     * Registers a <code>ScriptEngineFactory</code> to handle an extension.
     * Overrides any such association found using the Discovery mechanism.
     *
     * @param extension The extension type  to be associated with the
     * <code>ScriptEngineFactory</code>.
     * @param factory The class to associate with the given extension.
     * @throws NullPointerException if any of the parameters is null.
     */
    public function registerEngineExtension(string $extension, ScriptEngineFactoryInterface $factory): void
    {
        $this->extensionAssociations[$extension] = $factory;
    }

    /** Set of script engine factories discovered. */
    private $engineSpis = [];

    /** Map of engine name to script engine factory. */
    private $nameAssociations = [];

    /** Map of script file extension to script engine factory. */
    private $extensionAssociations = [];

    /** Map of script script MIME type to script engine factory. */
    //private $mimeTypeAssociations = [];

    /** Global bindings associated with script engines created by this manager. */
    private $globalScope;
}
