<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis\Actuator\StrategyDeterminator;

use Innmind\Homeostasis\{
    Actuator\StrategyDeterminator,
    Strategy,
    State,
    Exception\StrategyNotDeterminable,
};
use Innmind\Math\{
    DefinitionSet\Set,
    Statistics\Mean,
    Algebra\Number,
};
use Innmind\Immutable\{
    Map,
    Sequence,
};
use function Innmind\Immutable\assertMap;

final class SetTooShort implements StrategyDeterminator
{
    private Map $strategies;

    public function __construct(Map $strategies)
    {
        assertMap(Set::class, Strategy::class, $strategies, 1);

        $this->strategies = $strategies;
    }

    public function __invoke(Sequence $states): Strategy
    {
        if ($states->size() > 4) {
            throw new StrategyNotDeterminable;
        }

        $mean = new Number\Number(0.5);

        if (!$states->empty()) {
            /** @var list<Number> */
            $states = $states->reduce(
                [],
                static function(array $states, State $state): array {
                    $states[] = $state->value();

                    return $states;
                }
            );
            $mean = new Mean(...$states);
        }

        return $this
            ->strategies
            ->reduce(
                Strategy::holdSteady(),
                static function(Strategy $expected, Set $set, Strategy $strategy) use ($mean): Strategy {
                    if ($set->contains($mean)) {
                        $expected = $strategy;
                    }

                    return $expected;
                }
            );
    }
}
