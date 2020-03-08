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
        $points = $states->reduce(
            Sequence::of(Pair::class),
            static function(Sequence $points, State $state): Sequence {
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
                    $delta = $point->key() - $previous;
                    $previous = $point->key();

                    if ($delta < $lowest) {
                        $lowest = $delta;
                    }

                    return $lowest;
                }
            );

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
