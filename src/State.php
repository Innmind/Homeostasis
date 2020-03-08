<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis;

use Innmind\Homeostasis\{
    Sensor\Measure,
    Exception\SumOfWeightsMustBeOne
};
use Innmind\Math\{
    Algebra\Number,
    Algebra\Integer,
    Statistics\Mean
};
use Innmind\TimeContinuum\PointInTime;
use Innmind\Immutable\{
    Sequence,
    Map,
};
use function Innmind\Immutable\unwrap;

final class State
{
    private PointInTime $time;
    private Map $measures;
    private Number $value;

    public function __construct(
        PointInTime $time,
        Map $measures
    ) {
        if (
            (string) $measures->keyType() !== 'string' ||
            (string) $measures->valueType() !== Measure::class
        ) {
            throw new \TypeError(sprintf(
                'Argument 2 must be of type Map<string, %s>',
                Measure::class
            ));
        }

        $weight = $measures->reduce(
            new Integer(0),
            static function(Number $weight, string $factor, Measure $measure): Number {
                return $weight->add(
                    $measure->weight()->value()
                );
            }
        );

        if (!$weight->equals(new Integer(1))) {
            throw new SumOfWeightsMustBeOne;
        }

        $this->time = $time;
        $this->measures = $measures;
        $this->value = new Mean(
            ...unwrap($measures->reduce(
                Sequence::of(Number::class),
                static function(Sequence $weighted, string $factor, Measure $measure): Sequence {
                    return $weighted->add(
                        $measure->value()->multiplyBy($measure->weight()->value())
                    );
                }
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

    public function __toString(): string
    {
        return $this->value->toString();
    }
}
