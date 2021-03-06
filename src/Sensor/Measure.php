<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis\Sensor;

use Innmind\Homeostasis\{
    Sensor\Measure\Weight,
    Exception\OutOfRangeMeasure,
};
use Innmind\TimeContinuum\PointInTime;
use Innmind\Math\{
    Algebra\Number,
    Algebra\Integer,
    DefinitionSet\Set,
    DefinitionSet\Range,
};

final class Measure
{
    private static ?Set $definitionSet = null;

    private PointInTime $time;
    private Number $value;
    private Weight $weight;

    public function __construct(
        PointInTime $time,
        Number $value,
        Weight $weight
    ) {
        if (!self::definitionSet()->contains($value)) {
            throw new OutOfRangeMeasure;
        }

        $this->time = $time;
        $this->value = $value;
        $this->weight = $weight;
    }

    public function time(): PointInTime
    {
        return $this->time;
    }

    public function value(): Number
    {
        return $this->value;
    }

    public function weight(): Weight
    {
        return $this->weight;
    }

    public function toString(): string
    {
        return $this->value->toString();
    }

    public static function definitionSet(): Set
    {
        return self::$definitionSet ??= Range::inclusive(
            new Integer(0),
            new Integer(1),
        );
    }
}
