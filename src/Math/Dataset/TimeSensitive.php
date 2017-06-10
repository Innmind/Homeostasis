<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis\Math\Dataset;

use Innmind\Homeostasis\State;
use Innmind\Math\Regression\Dataset;
use Innmind\Immutable\{
    SetInterface,
    Stream,
    Pair
};

final class TimeSensitive
{
    /**
     * @param SetInterface<State> $states
     */
    public function __invoke(SetInterface $states): Dataset
    {
        $points = $states
            ->sort(function(State $a, State $b): bool {
                return $a->time()->aheadOf($b->time());
            })
            ->reduce(
                new Stream(Pair::class),
                static function(Stream $points, State $state): Stream {
                    $key = 0;

                    if ($points->size() > 0) {
                        $key = $state
                            ->time()
                            ->elapsedSince(
                                $points->first()->value()->time()
                            )
                            ->milliseconds();

                    }
                    return $points->add(
                        new Pair($key, $state)
                    );
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

        return Dataset::fromArray($points);
    }
}
