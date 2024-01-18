<?php

use PHPUnit\Framework\TestCase;
use SashaBo\NginxConfParser\Parser;
use SashaBo\NginxConfParser\Row;

/**
 * @internal
 *
 * @coversNothing
 */
final class RealExampleTest extends TestCase
{
    /**
     * @var array<Row>
     */
    private array $parsed = [];

    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->parsed = Parser::parseFile(__DIR__ . '/example/nginx.conf', true);
    }

    public function testArrayOfRows(): void
    {
        foreach ($this->parsed as $row) {
            $this->assertInstanceOf(Row::class, $row);
        }
    }

    public function testUserRow(): void
    {
        $this->assertSame('user', $this->parsed[0]->getName());
        $this->assertSame(['root'], $this->parsed[0]->getValue());
        $this->assertSame([], $this->parsed[0]->getRows());
    }

    public function testWorkerProcessesRow(): void
    {
        $this->assertSame('worker_processes', $this->parsed[1]->getName());
        $this->assertSame(['auto'], $this->parsed[1]->getValue());
        $this->assertSame([], $this->parsed[1]->getRows());
    }

    public function testHttpBlock(): void
    {
        $this->assertSame('http', $this->parsed[5]->getName());
        $this->assertSame([], $this->parsed[5]->getValue());
        $this->assertCount(11, $this->parsed[5]->getRows());
    }

    public function testJpegMimeType(): void
    {
        $mimeTypesBlock = $this->parsed[5]->getRows()[0];
        $this->assertSame('types', $mimeTypesBlock->getName());
        $this->assertSame([], $mimeTypesBlock->getValue());
        $this->assertSame('image/jpeg', $mimeTypesBlock->getRows()[4]->getName());
        $this->assertSame(['jpeg', 'jpg'], $mimeTypesBlock->getRows()[4]->getValue());
    }
}
