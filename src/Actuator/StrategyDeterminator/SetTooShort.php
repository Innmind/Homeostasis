<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis\Actuator\StrategyDeterminator;

use Innmind\Homeostasis\{
    Actuator\StrategyDeterminator,
    Strategy,
    State,
    Exception\StrategyNotDeterminable
};
use Innmind\Math\{
    DefinitionSet\Set,
    Statistics\Mean,
    Algebra\Number\Number
};
use Innmind\Immutable\{
    Map,
    Sequence,
};

final class SetTooShort implements StrategyDeterminator
{
    private Map $strategies;

    public function __construct(Map $strategies)
    {
        if (
            (string) $strategies->keyType() !== Set::class ||
            (string) $strategies->valueType() !== Strategy::class
        ) {
            throw new \TypeError(sprintf(
                'Argument 1 must be of type Map<%s, %s>',
                Set::class,
                Strategy::class
            ));
        }

        $this->strategies = $strategies;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(Sequence $states): Strategy
    {
        if ($states->size() > 4) {
            throw new StrategyNotDeterminable;
        }

        $mean = new Number(0.5);

        if ($states->size() > 0) {
            $mean = new Mean(
                ...$states->reduce(
                    [],
                    static function(array $values, State $state): array {
                        $states[] = $state->value();

                        return $states;
                    }
                ),
            );
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
