<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use BpmPlatform\Model\Xml\Impl\Util\StringUtil;

class StringUtilTest extends TestCase
{
    public function testStringListSplit(): void
    {
        $this->assertEmpty(StringUtil::splitCommaSeparatedList(null));
        $this->assertEmpty(StringUtil::splitCommaSeparatedList(''));
        $this->assertEmpty(StringUtil::splitCommaSeparatedList('  '));
        $this->assertContains('a', StringUtil::splitCommaSeparatedList('a'));
        foreach (['a', 'b'] as $value) {
            $this->assertContains($value, StringUtil::splitCommaSeparatedList('a,b'));
        }
        $this->assertCount(2, StringUtil::splitCommaSeparatedList('a,b'));
        foreach (['a', 'b', 'c'] as $value) {
            $this->assertContains($value, StringUtil::splitCommaSeparatedList('a , b, c '));
        }
        $this->assertCount(3, StringUtil::splitCommaSeparatedList('a , b, c '));
        $this->assertContains('${}', StringUtil::splitCommaSeparatedList('${}'));
        $this->assertContains('#{ }', StringUtil::splitCommaSeparatedList(' #{ } '));
        foreach (['#{}', '${a}', '#{b}'] as $value) {
            $this->assertContains($value, StringUtil::splitCommaSeparatedList(' #{}, ${a}, #{b} '));
        }
        $this->assertCount(3, StringUtil::splitCommaSeparatedList(' #{}, ${a}, #{b} '));
        foreach (['a', '${b}', '#{c}'] as $value) {
            $this->assertContains($value, StringUtil::splitCommaSeparatedList(' a, ${b}, #{c} '));
        }
        $this->assertCount(3, StringUtil::splitCommaSeparatedList(' a, ${b}, #{c} '));
        foreach (['#{a}', 'b', 'c', '${d}'] as $value) {
            $this->assertContains($value, StringUtil::splitCommaSeparatedList(' #{a}, b, ,c ,${d} '));
        }
        $this->assertCount(4, StringUtil::splitCommaSeparatedList(' #{a}, b, ,c ,${d} '));
        foreach (['#{a(b,c)}', 'd', 'e', '${fg(h , i , j)}'] as $value) {
            $this->assertContains($value, StringUtil::splitCommaSeparatedList(' #{a(b,c)}, d, ,e ,${fg(h , i , j)} '));
        }
        $this->assertCount(4, StringUtil::splitCommaSeparatedList(' #{a(b,c)}, d, ,e ,${fg(h , i , j)} '));
        foreach (['#{a == (b, c)}', 'd = e', 'f', '${fg(h , i , j)}'] as $value) {
            $this->assertContains($value, StringUtil::splitCommaSeparatedList(
                ' #{a == (b, c)}, d = e, f ,${fg(h , i , j)} '
            ));
        }
        $this->assertCount(4, StringUtil::splitCommaSeparatedList(' #{a == (b, c)}, d = e, f ,${fg(h , i , j)} '));
    }

    public function testStringListJoin(): void
    {
        $this->assertNull(StringUtil::joinCommaSeparatedList(null));
        $arr = [];
        $this->assertEquals('', StringUtil::joinCommaSeparatedList($arr));
        $arr[] = 'a';
        $this->assertEquals('a', StringUtil::joinCommaSeparatedList($arr));
        $arr[] = 'b';
        $this->assertEquals('a, b', StringUtil::joinCommaSeparatedList($arr));
        $arr[] = '${a,b,c}';
        $this->assertEquals('a, b, ${a,b,c}', StringUtil::joinCommaSeparatedList($arr));
        $arr[] = 'foo';
        $this->assertEquals('a, b, ${a,b,c}, foo', StringUtil::joinCommaSeparatedList($arr));
        $arr[] = '#{bar(e,f,g)}';
        $this->assertEquals('a, b, ${a,b,c}, foo, #{bar(e,f,g)}', StringUtil::joinCommaSeparatedList($arr));
    }
}
