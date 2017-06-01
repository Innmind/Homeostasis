<?php
declare(strict_types = 1);

namespace Tests\Innmind\Homeostasis\Sensor;

use Innmind\Homeostasis\Sensor\{
    Measure,
    Measure\Weight
};
use Innmind\Math\{
    Algebra\Number\Number,
    DefinitionSet\Set
};
use Innmind\TimeContinuum\PointInTimeInterface;
use PHPUnit\Framework\TestCase;

class MeasureTest extends TestCase
{
    public function testInterface()
    {
        $measure = new Measure(
            $time = $this->createMock(PointInTimeInterface::class),
            $value = new Number(0.5),
            $weight = new Weight(new Number(1))
        );

        $this->assertSame($time, $measure->time());
        $this->assertSame($value, $measure->value());
        $this->assertSame($weight, $measure->weight());
        $this->assertSame('0.5', (string) $measure->value());
    }

    public function testDefinitionSet()
    {
        $set = Measure::definitionSet();

        $this->assertInstanceOf(Set::class, $set);
        $this->assertSame('[0;1]', (string) $set);
    }

    /**
     * @expectedException Innmind\Homeostasis\Exception\OutOfRangeMeasure
     */
    public function testThrowWhenValueLowerThanSet()
    {
        new Measure(
            $this->createMock(PointInTimeInterface::class),
            new Number(-0.1),
            new Weight(new Number(1))
        );
    }

    /**
     * @expectedException Innmind\Homeostasis\Exception\OutOfRangeMeasure
     */
    public function testThrowWhenValueHigherThanSet()
    {
        new Measure(
            $this->createMock(PointInTimeInterface::class),
            new Number(-0.1),
            new Weight(new Number(1))
        );
    }
}
