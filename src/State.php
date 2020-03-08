<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis;

use Innmind\Homeostasis\{
    Sensor\Measure,
    Exception\SumOfWeightsMustBeOne,
};
use Innmind\Math\{
    Algebra\Number,
    Algebra\Integer,
    Statistics\Mean,
};
use Innmind\TimeContinuum\PointInTime;
use Innmind\Immutable\{
    Sequence,
    Map,
};
use function Innmind\Immutable\{
    unwrap,
    assertMap,
};

final class State
{
    private PointInTime $time;
    /** @var Map<string, Measure> */
    private Map $measures;
    private Number $value;

    /**
     * @param Map<string, Measure> $measures
     */
    public function __construct(PointInTime $time, Map $measures)
    {
        assertMap('string', Measure::class, $measures, 2);

        $weight = $measures->reduce(
            new Integer(0),
            static function(Number $weight, string $factor, Measure $measure): Number {
                return $weight->add(
                    $measure->weight()->value(),
                );
            },
        );

        if (!$weight->equals(new Integer(1))) {
            throw new SumOfWeightsMustBeOne;
        }

        $this->time = $time;
        $this->measures = $measures;
        $this->value = new Mean(
            ...unwrap($measures->values()->mapTo(
                Number::class,
                static fn(Measure $measure): Number => $measure->value()->multiplyBy(
                    $measure->weight()->value(),
                ),
            )),
        );
    }

    public function time(): PointInTime
    {
        return $this->time;
    }

    public function factor(string $name): Measure
    {
        return $this->measures->get($name);
    }

    /**
     * @return Map<string, Measure>
     */
    public function measures(): Map
    {
        return $this->measures;
    }

    public function value(): Number
    {
        return $this->value;
    }

    public function toString(): string
    {
        return $this->value->toString();
    }
}
