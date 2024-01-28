<?php

use PHPUnit\Framework\TestCase;
use SashaBo\NginxConfParser\Finder;
use SashaBo\NginxConfParser\Parser;
use SashaBo\NginxConfParser\Row;

/**
 * @internal
 *
 * @coversNothing
 */
final class FinderTest extends TestCase
{
    private const SOURCE = '
        aaa_aaa 1;
        bbb_bbb 2;
        ccc_ccc {
            aaa_aaa 3;
            bbb_bbb {
                ccc_ccc 4;
            }
            aaa_aaa 5;
        }
        bbb_bbb 6;
    ';

    public function testFindByName(): void
    {
        $rows = Parser::parseString(self::SOURCE);
        $found = Finder::findByName($rows, 'aaa_aaa');
        $this->assertCount(1, $found);
        $this->assertEquals(1, $found[0]->values[0]);
        $found = Finder::findByName($rows, 'bbb_bbb');
        $this->assertCount(2, $found);
        $this->assertEquals(2, $found[0]->values[0]);
        $this->assertEquals(6, $found[1]->values[0]);
        $found = Finder::findByName($rows, 'xxx_xxx');
        $this->assertCount(0, $found);
    }

    public function testFindOneByName(): void
    {
        $rows = Parser::parseString(self::SOURCE);
        $found = Finder::findOneByName($rows, 'bbb_bbb');
        $this->assertInstanceOf(Row::class, $found);
        $this->assertEquals(2, $found->values[0]);
        $found = Finder::findOneByName($rows, 'xxx_xxx');
        $this->assertSame(null, $found);
    }

    public function testFindByPath(): void
    {
        $rows = Parser::parseString(self::SOURCE);
        $found = Finder::findByPath($rows, ['aaa_aaa']);
        $this->assertCount(1, $found);
        $this->assertEquals(1, $found[0]->values[0]);
        $found = Finder::findByPath($rows, ['bbb_bbb']);
        $this->assertCount(2, $found);
        $this->assertEquals(2, $found[0]->values[0]);
        $this->assertEquals(6, $found[1]->values[0]);
        $found = Finder::findByPath($rows, ['xxx_xxx']);
        $this->assertCount(0, $found);
        $found = Finder::findByPath($rows, ['ccc_ccc', 'bbb_bbb']);
        $this->assertCount(1, $found);
        $this->assertCount(0, $found[0]->values);
        $this->assertCount(1, $found[0]->rows);
        $this->assertSame('ccc_ccc', $found[0]->rows[0]->name);
    }

    public function testFindOneByPath(): void
    {
        $rows = Parser::parseString(self::SOURCE);
        $found = Finder::findOneByPath($rows, ['bbb_bbb']);
        $this->assertInstanceOf(Row::class, $found);
        $this->assertEquals(2, $found->values[0]);
        $found = Finder::findOneByPath($rows, ['xxx_xxx']);
        $this->assertSame(null, $found);
        $found = Finder::findOneByPath($rows, ['ccc_ccc', 'bbb_bbb']);
        $this->assertInstanceOf(Row::class, $found);
        $this->assertCount(0, $found->values);
        $this->assertCount(1, $found->rows);
        $this->assertSame('ccc_ccc', $found->rows[0]->name);
    }
}
