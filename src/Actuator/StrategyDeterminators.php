<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis\Actuator;

use Innmind\Homeostasis\{
    Strategy,
    Actuator\StrategyDeterminator\Delegate,
    Actuator\StrategyDeterminator\SetTooShort,
    Actuator\StrategyDeterminator\WaterLane,
    Actuator\StrategyDeterminator\CrossLane,
    Math\Dataset\Augment
};
use Innmind\Math\{
    DefinitionSet\Range,
    DefinitionSet\Set,
    Algebra\Number\Number,
    Algebra\Integer
};
use Innmind\Immutable\Map;

final class StrategyDeterminators
{
    private static $default;

    public static function default(): StrategyDeterminator
    {
        if (self::$default instanceof StrategyDeterminator) {
            return self::$default;
        }

        $veryHigh = new Range(false, new Number(0.8), new Number(1), true);// ]0.8;1]
        $high = new Range(false, new Number(0.6), new Number(0.8), true);// ]0.6;0.8]
        $mid = new Range(true, new Number(0.4), new Number(0.6), true);// [0.4;0.6]
        $low = new Range(true, new Number(0.2), new Number(0.4), false);// [0.2;0.4[
        $veryLow = new Range(true, new Number(0), new Number(0.2), false);// [0;0.2[
        $predict = new Augment(new Integer(1));

        return self::$default = new Delegate(
            new SetTooShort(
                (new Map(Set::class, Strategy::class))
                    ->put($veryHigh, Strategy::dramaticDecrease())
                    ->put($high, Strategy::decrease())
                    ->put($mid, Strategy::holdSteady())
                    ->put($low, Strategy::increase())
                    ->put($veryLow, Strategy::dramaticIncrease())
            ),
            new WaterLane(
                $veryHigh,
                $predict,
                Strategy::dramaticDecrease(),
                Strategy::decrease()
            ),
            new WaterLane(
                $high,
                $predict,
                Strategy::decrease(),
                Strategy::holdSteady()
            ),
            new WaterLane(
                $mid,
                $predict,
                Strategy::holdSteady(),
                Strategy::holdSteady()
            ),
            new WaterLane(
                $low,
                $predict,
                Strategy::increase(),
                Strategy::holdSteady()
            ),
            new WaterLane(
                $veryLow,
                $predict,
                Strategy::increase(),
                Strategy::dramaticIncrease()
            ),
            new CrossLane($predict)
        );
    }
}
