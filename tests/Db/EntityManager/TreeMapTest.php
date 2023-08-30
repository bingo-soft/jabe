<?php

namespace Tests\Db\EntityManager;

use PHPUnit\Framework\TestCase;
use Jabe\Impl\Db\EntityManager\Operation\Comparator\{
    ComparatorInterface,
    TreeMap,
    TreeSet
};

class TreeMapTest extends TestCase
{
    public function testTreeMapComparators(): void
    {
        $tm = new TreeMap(new class () implements ComparatorInterface {
            public function compareTo($obj1, $obj2): int
            {
                if ($obj1 < $obj2) {
                    return -1;
                }
                if ($obj1 > $obj2) {
                    return 1;
                }
                return 0;
            }
        });

        $tm->put(100, new class (new class () implements ComparatorInterface {
            public function compareTo($obj1, $obj2): int
            {
                if ($obj1 < $obj2) {
                    return 1;
                }
                if ($obj1 > $obj2) {
                    return -1;
                }
                return 0;
            }
        }) extends TreeSet
        {
        });
        $s = $tm->get(100);
        $s->add(100);
        $s->add(120);
        $s->add(80);
        $s->add(80);

        $tm->put(50, new class (new class () implements ComparatorInterface {
            public function compareTo($obj1, $obj2): int
            {
                if ($obj1 < $obj2) {
                    return 1;
                }
                if ($obj1 > $obj2) {
                    return -1;
                }
                return 0;
            }
        }) extends TreeSet
        {
        });
        $s = $tm->get(50);
        $s->add(50);

        $tm->put(0, new class (new class () implements ComparatorInterface {
            public function compareTo($obj1, $obj2): int
            {
                if ($obj1 < $obj2) {
                    return 1;
                }
                if ($obj1 > $obj2) {
                    return -1;
                }
                return 0;
            }
        }) extends TreeSet
        {
        });
        $s = $tm->get(0);
        $s->add(0);

        $this->assertEquals([0, 50, 100], array_keys($tm->getArrayCopy()));

        $this->assertEquals([1 => 120, 0 => 100, 2 => 80], $tm->get(100)->getArrayCopy());
    }
}
