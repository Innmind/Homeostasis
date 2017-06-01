<?php
declare(strict_types = 1);

namespace Tests\Innmind\Homeostasis\Sensor\Measure;

use Innmind\Homeostasis\Sensor\Measure\Weight;
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
        $this->assertSame('0.5', (string) $weight->value());
    }

    public function testDefinitionSet()
    {
        $set = Weight::definitionSet();

        $this->assertInstanceOf(Set::class, $set);
        $this->assertSame('[0;1]', (string) $set);
        $this->assertSame($set, Weight::definitionSet());
    }

    /**
     * @expectedException Innmind\Homeostasis\Exception\OutOfRangeMeasureWeight
     */
    public function testThrowWhenValueLowerThanSet()
    {
        new Weight(new Number(-0.1));
    }

    /**
     * @expectedException Innmind\Homeostasis\Exception\OutOfRangeMeasureWeight
     */
    public function testThrowWhenValueHigherThanSet()
    {
        new Weight(new Number(1.1));
    }
}
