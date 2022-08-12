<?php

namespace Jabe\Impl\Scripting\Env;

interface ScriptEnvResolverInterface
{
    /**
     * Resolves a set of environment scripts for a given script language.
     *
     * @param the script language to resolve env scripts for.
     * @return an array of environment script sources or null if this
     * resolver does not provide any scripts for the given language
     */
    public function resolve(string $language): array;
}
