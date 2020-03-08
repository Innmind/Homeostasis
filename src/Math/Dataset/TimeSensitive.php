<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis\Math\Dataset;

use Innmind\Homeostasis\State;
use Innmind\Math\Regression\Dataset;
use Innmind\Immutable\{
    Sequence,
    Pair,
};

final class TimeSensitive
{
    /**
     * @param Sequence<State> $states
     */
    public function __invoke(Sequence $states): Dataset
    {
        /** @var Sequence<Pair<int, State>> */
        $points = $states->reduce(
            Sequence::of(Pair::class),
            static function(Sequence $points, State $state): Sequence {
                /** @var Sequence<Pair<int, State>> */
                $points = $points;

                $key = 0;

                if (!$points->empty()) {
                    $key = $state
                        ->time()
                        ->elapsedSince(
                            $points->first()->value()->time()
                        )
                        ->milliseconds();
                }

                return ($points)(new Pair($key, $state));
            }
        );

        $previous = $points->first()->key();
        $lowestGap = $points
            ->drop(1)
            ->reduce(
                INF,
                static function(float $lowest, Pair $point) use (&$previous): float {
                    /**
                     * @psalm-suppress MixedOperand
                     * @var int
                     */
                    $delta = $point->key() - $previous;
                    $previous = $point->key();

                    if ($delta < $lowest) {
                        $lowest = $delta;
                    }

                    return $lowest;
                }
            );

        /**
         * @psalm-suppress InvalidScalarArgument Typewise the map() is wrong but Sequence doesn't verify types in Pair
         * @var list<array{0: float, 1: int|float}>
         */
        $points = $points
            ->map(static function(Pair $point) use ($lowestGap): Pair {
                return new Pair(
                    $point->key() / $lowestGap,
                    $point->value()
                );
            })
            ->reduce(
                [],
                static function(array $points, Pair $point): array {
                    $points[] = [$point->key(), $point->value()->value()];

                    return $points;
                }
            );

        return Dataset::of($points);
    }
}
