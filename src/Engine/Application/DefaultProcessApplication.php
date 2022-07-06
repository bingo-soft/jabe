<?php

namespace Jabe\Engine\Application;

abstract class DefaultProcessApplication
{
    public const DEFAULT_META_INF_PROCESSES_XML = "META-INF/processes.xml";

    /**
     * Allows specifying the name of the process application.
     * Overrides the {@code name} property.
     */
    public function value(): string
    {
        return "";
    }

    /**
     * Allows specifying the name of the process application.
     * Only applies if the {@code value} property is not set.
     */
    public function name(): string
    {
        return "";
    }

    /**
     * Returns the location(s) of the <code>processes.xml</code> deployment descriptors.
     * The default value is<code>{META-INF/processes.xml}</code>. The provided path(s)
     * must be resolvable through the {@link ClassLoader#getResourceAsStream(String)}-Method
     * of the classloader returned  by the {@link AbstractProcessApplication#getProcessApplicationClassloader()}
     * method provided by the process application.
     *
     * @return string the location of the <code>processes.xml</code> file.
     */
    public function deploymentDescriptors(): string
    {
        self::DEFAULT_META_INF_PROCESSES_XML;
    }
}
