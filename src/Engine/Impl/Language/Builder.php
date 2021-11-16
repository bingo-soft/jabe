<?php

namespace BpmPlatform\Engine\Impl\Language;

use BpmPlatform\Engine\Impl\Expression\{
    ELContext,
    ELException,
    ELResolver,
    FunctionMapper,
    VariableMapper
};

class Builder implements TreeBuilder
{
    protected $features = [];

    public function __construct(?array $features = [])
    {
        $this->features = $features;
    }

    /**
     * @return <code>true</code> iff the specified feature is supported.
     */
    public function isEnabled(string $feature): bool
    {
        return in_array($feature, $this->features);
    }

    /**
     * Parse expression.
     */
    public function build(string $expression): Tree
    {
        try {
            return $this->createParser($expression)->tree();
        } catch (ScanException $e) {
            throw new TreeBuilderException($expression, $e->position, $e->encountered, $e->expected, $e->getMessage());
        } catch (ParseException $e) {
            throw new TreeBuilderException($expression, $e->position, $e->encountered, $e->expected, $e->getMessage());
        } catch (\Exception $e) {
            throw new \Exception($e->getTraceAsString());
        }
    }

    protected function createParser(string $expression): Parser
    {
        return new Parser($this, $expression);
    }

    /**
     * Dump out abstract syntax tree for a given expression
     *
     * @param expression the expression string
     */
    public static function dump(string $expression): string
    {
        $tree = null;
        $tree = (new Builder([Feature::METHOD_INVOCATIONS]))->build($expression);
        return NodePrinter::dump($tree->getRoot());
    }
}
