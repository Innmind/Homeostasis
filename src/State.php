<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis;

use Innmind\Homeostasis\{
    Sensor\Measure,
    Exception\InvalidMeasures,
    Exception\SumOfWeightsMustBeOne
};
use Innmind\Math\{
    Algebra\Number,
    Algebra\Integer,
    Statistics\Mean
};
use Innmind\TimeContinuum\PointInTimeInterface;
use Innmind\Immutable\{
    Stream,
    MapInterface
};

final class State
{
    private $time;
    private $measures;
    private $value;

    public function __construct(
        PointInTimeInterface $time,
        MapInterface $measures
    ) {
        if (
            (string) $measures->keyType() !== 'string' ||
            (string) $measures->valueType() !== Measure::class
        ) {
            throw new InvalidMeasures;
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
            ...$measures->reduce(
                new Stream(Number::class),
                static function(Stream $weighted, string $factor, Measure $measure): Stream {
                    return $weighted->add(
                        $measure->value()->multiplyBy($measure->weight()->value())
                    );
                }
            )
        );
    }

    public function time(): PointInTimeInterface
    {
        return $this->time;
    }

    public function factor(string $name): Measure
    {
        return $this->measures->get($name);
    }

    /**
     * @return MapInterface<string, Measure>
     */
    public function measures(): MapInterface
    {
        return $this->measures;
    }

    public function value(): Number
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
