<?php

use PHPUnit\Framework\TestCase;
use SashaBo\NginxConfParser\Parser;

/**
 * @internal
 *
 * @coversNothing
 */
final class PositioningTest extends TestCase
{
    public function testSimpleRow(): void
    {
        $source = ' aaa aaa; ';
        $rows = Parser::parseString($source);
        $this->assertSame('aaa', $rows[0]->name);
        $this->assertSame(['aaa'], $rows[0]->values);
        $this->assertSame(1, $rows[0]->position);
        $this->assertSame(0, $rows[0]->line);
        $this->assertSame(1, $rows[0]->linePosition);
        $this->assertSame(8, $rows[0]->length);
    }

    public function testThirdRow(): void
    {
        $source = " \n \n aaa aaa; bbb bbb bbb; ";
        $rows = Parser::parseString($source);
        $this->assertSame('bbb', $rows[1]->name);
        $this->assertSame(['bbb', 'bbb'], $rows[1]->values);
        $this->assertSame(14, $rows[1]->position);
        $this->assertSame(2, $rows[1]->line);
        $this->assertSame(10, $rows[1]->linePosition);
        $this->assertSame(12, $rows[1]->length);
    }

    public function testParameterReplacement(): void
    {
        $source = "aaa aaa;\nbbb bbb bbb; cc ccc;";
        $replaced = "aaa aaa;\nxxx xxx; cc ccc;";
        $rows = Parser::parseString($source);
        $this->assertSame($replaced, substr_replace($source, 'xxx xxx;', $rows[1]->position, $rows[1]->length));
    }

    public function testBlock(): void
    {
        $source = " aaa aaa;\n bbb   {\nccc ccc;\n ddd ddd;\n} ";
        $rows = Parser::parseString($source);
        $this->assertSame('bbb', $rows[1]->name);
        $this->assertSame([], $rows[1]->values);
        $this->assertSame(11, $rows[1]->position);
        $this->assertSame(1, $rows[1]->line);
        $this->assertSame(1, $rows[1]->linePosition);
        $this->assertSame(28, $rows[1]->length);
    }

    public function testBlockReplacement(): void
    {
        $source = " aaa aaa;\n bbb   {\nccc ccc;\n ddd ddd;\n} eee eee; ";
        $replaced = " aaa aaa;\n xxx xxx; eee eee; ";
        $rows = Parser::parseString($source);
        $this->assertSame($replaced, substr_replace($source, 'xxx xxx;', $rows[1]->position, $rows[1]->length));
    }
}
