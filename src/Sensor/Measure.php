<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis\Sensor;

use Innmind\Homeostasis\{
    Sensor\Measure\Weight,
    Exception\OutOfRangeMeasure
};
use Innmind\TimeContinuum\PointInTimeInterface;
use Innmind\Math\{
    Algebra\Number,
    Algebra\Integer,
    DefinitionSet\Set,
    DefinitionSet\Range
};

final class Measure
{
    private static $definitionSet;

    private $time;
    private $value;
    private $weight;

    public function __construct(
        PointInTimeInterface $time,
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

    public function time(): PointInTimeInterface
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

    public function __toString(): string
    {
        return (string) $this->value;
    }

    public static function definitionSet(): Set
    {
        return self::$definitionSet ?? self::$definitionSet = Range::inclusive(
            new Integer(0),
            new Integer(1)
        );
    }
}
