<?php

use PHPUnit\Framework\TestCase;
use SashaBo\NginxConfParser\Finder;
use SashaBo\NginxConfParser\Parser;
use SashaBo\NginxConfParser\VariableReplacer;

/**
 * @internal
 *
 * @coversNothing
 */
final class VariableReplacerTest extends TestCase
{
    private const SOURCE = '
        first_parameter test-$aaa;
        second_parameter {
            sub_parameter $bbb;
        }
    ';

    public function testReplacement(): void
    {
        $rows = Parser::parseString(self::SOURCE);
        $rows = VariableReplacer::replace($rows, ['aaa' => 'xxx', 'bbb' => 'yyy']);
        $found = Finder::findOneByName($rows, 'first_parameter');
        $this->assertSame('test-xxx', $found->values[0]);
        $found = Finder::findOneByPath($rows, ['second_parameter', 'sub_parameter']);
        $this->assertSame('yyy', $found->values[0]);
    }

    public function testIgnoreMissed(): void
    {
        $rows = Parser::parseString(self::SOURCE);
        $rows = VariableReplacer::replace($rows, ['aaa' => 'xxx']);
        $found = Finder::findOneByName($rows, 'first_parameter');
        $this->assertSame('test-xxx', $found->values[0]);
        $found = Finder::findOneByPath($rows, ['second_parameter', 'sub_parameter']);
        $this->assertSame('$bbb', $found->values[0]);
    }

    public function testSetMissedEmpty(): void
    {
        $rows = Parser::parseString(self::SOURCE);
        $rows = VariableReplacer::replace($rows, ['aaa' => 'xxx'], VariableReplacer::MISSED_SET_EMPTY);
        $found = Finder::findOneByName($rows, 'first_parameter');
        $this->assertSame('test-xxx', $found->values[0]);
        $found = Finder::findOneByPath($rows, ['second_parameter', 'sub_parameter']);
        $this->assertSame('', $found->values[0]);
    }

    public function testMissedException(): void
    {
        $rows = Parser::parseString(self::SOURCE);
        $exception = null;

        try {
            $rows = VariableReplacer::replace($rows, ['aaa' => 'xxx'], VariableReplacer::MISSED_THROW_EXCEPTION);
        } catch (Exception $exception) {
        }
        $this->assertInstanceOf(Exception::class, $exception);
    }
}
