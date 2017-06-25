<?php
declare(strict_types = 1);

namespace Tests\Innmind\Homeostasis\Actuator;

use Innmind\Homeostasis\Actuator\{
    StrategyDeterminators,
    StrategyDeterminator
};
use PHPUnit\Framework\TestCase;

class StrategyDeterminatorsTest extends TestCase
{
    public function testDefault()
    {
        $determinator = StrategyDeterminators::default();

        $this->assertInstanceOf(StrategyDeterminator::class, $determinator);
        $this->assertSame($determinator, StrategyDeterminators::default());
    }
}
