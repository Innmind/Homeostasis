<?php
declare(strict_types = 1);

namespace Tests\Innmind\Homeostasis\Sensor\Measure;

use Innmind\Homeostasis\{
    Sensor\Measure\Weight,
    Exception\OutOfRangeMeasureWeight,
};
use Innmind\Math\{
    Algebra\Number\Number,
    DefinitionSet\Set
};
use PHPUnit\Framework\TestCase;

class WeightTest extends TestCase
{
    public function testInterface()
    {
        $weight = new Weight($value = new Number(0.5));

        $this->assertSame($value, $weight->value());
        $this->assertSame('0.5', $weight->value()->toString());
        $this->assertSame('0.5', $weight->toString());
    }

    public function testDefinitionSet()
    {
        $set = Weight::definitionSet();

        $this->assertInstanceOf(Set::class, $set);
        $this->assertSame('[0;1]', $set->toString());
        $this->assertSame($set, Weight::definitionSet());
    }

    public function testThrowWhenValueLowerThanSet()
    {
        $this->expectException(OutOfRangeMeasureWeight::class);

        new Weight(new Number(-0.1));
    }

    public function testThrowWhenValueHigherThanSet()
    {
        $this->expectException(OutOfRangeMeasureWeight::class);

        new Weight(new Number(1.1));
    }
}
