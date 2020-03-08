<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis\Sensor\Measure;

use Innmind\Homeostasis\Exception\OutOfRangeMeasureWeight;
use Innmind\Math\{
    Algebra\Number,
    Algebra\Integer,
    DefinitionSet\Set,
    DefinitionSet\Range
};

final class Weight
{
    private static ?Set $definitionSet = null;

    private Number $value;

    public function __construct(Number $value)
    {
        if (!self::definitionSet()->contains($value)) {
            throw new OutOfRangeMeasureWeight;
        }

        $this->value = $value;
    }

    public function value(): Number
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value->toString();
    }

    public static function definitionSet(): Set
    {
        return self::$definitionSet ?? self::$definitionSet = Range::inclusive(
            new Integer(0),
            new Integer(1)
        );
    }
}
