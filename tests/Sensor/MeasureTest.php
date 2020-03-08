<?php
declare(strict_types = 1);

namespace Tests\Innmind\Homeostasis\Sensor;

use Innmind\Homeostasis\{
    Sensor\Measure,
    Sensor\Measure\Weight,
    Exception\OutOfRangeMeasure,
};
use Innmind\Math\{
    Algebra\Number\Number,
    DefinitionSet\Set
};
use Innmind\TimeContinuum\PointInTime;
use PHPUnit\Framework\TestCase;

class MeasureTest extends TestCase
{
    public function testInterface()
    {
        $measure = new Measure(
            $time = $this->createMock(PointInTime::class),
            $value = new Number(0.5),
            $weight = new Weight(new Number(1))
        );

        $this->assertSame($time, $measure->time());
        $this->assertSame($value, $measure->value());
        $this->assertSame($weight, $measure->weight());
        $this->assertSame('0.5', $measure->value()->toString());
        $this->assertSame('0.5', $measure->toString());
    }

    public function testDefinitionSet()
    {
        $set = Measure::definitionSet();

        $this->assertInstanceOf(Set::class, $set);
        $this->assertSame('[0;1]', $set->toString());
        $this->assertSame($set, Measure::definitionSet());
    }

    public function testThrowWhenValueLowerThanSet()
    {
        $this->expectException(OutOfRangeMeasure::class);

        new Measure(
            $this->createMock(PointInTime::class),
            new Number(-0.1),
            new Weight(new Number(1))
        );
    }

    public function testThrowWhenValueHigherThanSet()
    {
        $this->expectException(OutOfRangeMeasure::class);

        new Measure(
            $this->createMock(PointInTime::class),
            new Number(-0.1),
            new Weight(new Number(1))
        );
    }
}
