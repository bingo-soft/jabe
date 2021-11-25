<?php

namespace BpmPlatform\Engine\Impl\Juel;

class TreeStore
{
    private $cache;
    private $builder;

    /**
     * Constructor.
     * @param builder the tree builder
     * @param cache the tree cache (may be <code>null</code>)
     */
    public function __construct(TreeBuilder $builder, ?TreeCache $cache = null)
    {
        $this->builder = $builder;
        $this->cache = $cache;
    }

    public function getBuilder(): TreeBuilder
    {
        return $this->builder;
    }

    /**
     * Get a {@link Tree}.
     * If a tree for the given expression is present in the cache, it is
     * taken from there; otherwise, the expression string is parsed and
     * the resulting tree is added to the cache.
     * @param expression expression string
     * @return expression tree
     */
    public function get(string $expression): Tree
    {
        if ($this->cache == null) {
            return $this->builder->build($expression);
        }
        $tree = $this->cache->get($expression);
        if ($tree == null) {
            $tree = $this->builder->build($expression);
            $this->cache->put($expression, $tree);
        }
        return $tree;
    }
}
