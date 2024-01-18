<?php

use PHPUnit\Framework\TestCase;
use SashaBo\NginxConfParser\IterableString;

/**
 * @internal
 *
 * @coversNothing
 */
final class IterableStringTest extends TestCase
{
    public function testForeach(): void
    {
        $source = "aaa bbb\nccc";
        $cnt = 0;
        $sum = '';
        foreach (new IterableString($source) as $char) {
            ++$cnt;
            $sum .= $char;
        }
        $this->assertEquals(strlen($source), $cnt);
        $this->assertEquals($source, $sum);
    }

    public function testPositioning(): void
    {
        $source = "aaa bbb\nccc\n dddd";
        for ($i = 0; $i < 3; ++$i) {
            $positions = [];
            $iterableString = new IterableString($source);
            foreach ($iterableString as $no => $char) {
                $positions[$no] = [
                    'position' => $iterableString->key(),
                    'line' => $iterableString->line(),
                    'linePosition' => $iterableString->linePosition(),
                ];
            }

            $this->assertCount(17, $positions);

            $this->assertEquals(0, $positions[0]['position']);
            $this->assertEquals(0, $positions[0]['line']);
            $this->assertEquals(0, $positions[0]['linePosition']);

            $this->assertEquals(1, $positions[1]['position']);
            $this->assertEquals(0, $positions[1]['line']);
            $this->assertEquals(1, $positions[1]['linePosition']);

            $this->assertEquals(7, $positions[7]['position']);
            $this->assertEquals(0, $positions[7]['line']);
            $this->assertEquals(7, $positions[7]['linePosition']);

            $this->assertEquals(8, $positions[8]['position']);
            $this->assertEquals(1, $positions[8]['line']);
            $this->assertEquals(0, $positions[8]['linePosition']);

            $this->assertEquals(9, $positions[9]['position']);
            $this->assertEquals(1, $positions[9]['line']);
            $this->assertEquals(1, $positions[9]['linePosition']);

            $this->assertEquals(12, $positions[12]['position']);
            $this->assertEquals(2, $positions[12]['line']);
            $this->assertEquals(0, $positions[12]['linePosition']);

            $this->assertEquals(16, $positions[16]['position']);
            $this->assertEquals(2, $positions[16]['line']);
            $this->assertEquals(4, $positions[16]['linePosition']);
        }
    }
}
