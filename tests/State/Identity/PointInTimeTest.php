<?php
declare(strict_types = 1);

namespace Tests\Innmind\Homeostasis\State\Identity;

use Innmind\Homeostasis\State\{
    Identity\PointInTime,
    Identity
};
use Innmind\TimeContinuum\PointInTimeInterface;
use PHPUnit\Framework\TestCase;

class PointInTimeTest extends TestCase
{
    public function testInterface()
    {
        $time = $this->createMock(PointInTimeInterface::class);
        $time
            ->expects($this->once())
            ->method('format')
            ->with($this->callback(function($format) {
                return (string) $format === 'Y-m-d\TH:i:s.uP';
            }))
            ->willReturn('foo');

        $identity = new PointInTime($time);

        $this->assertInstanceOf(Identity::class, $identity);
        $this->assertSame('foo', (string) $identity);
    }
}
