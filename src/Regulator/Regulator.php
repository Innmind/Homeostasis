<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis\Regulator;

use Innmind\Homeostasis\{
    Regulator as RegulatorInterface,
    Strategy,
    StateHistory,
    Factor,
    State,
    Sensor\Measure,
    Actuator,
    Actuator\StrategyDeterminator
};
use Innmind\TimeContinuum\Clock;
use Innmind\Immutable\{
    Set,
    Map
};

final class Regulator implements RegulatorInterface
{
    private Set $factors;
    private StateHistory $history;
    private Clock $clock;
    private StrategyDeterminator $strategyDeterminator;
    private Actuator $actuator;

    public function __construct(
        Set $factors,
        StateHistory $history,
        Clock $clock,
        StrategyDeterminator $strategyDeterminator,
        Actuator $actuator
    ) {
        if ((string) $factors->type() !== Factor::class) {
            throw new \TypeError(sprintf(
                'Argument 1 must be of type Set<%s>',
                Factor::class
            ));
        }

        $this->factors = $factors;
        $this->history = $history;
        $this->clock = $clock;
        $this->strategyDeterminator = $strategyDeterminator;
        $this->actuator = $actuator;
    }

    public function __invoke(): Strategy
    {
        $states = $this
            ->history
            ->add($this->createState())
            ->all();

        $strategy = ($this->strategyDeterminator)($states);

        $this->actuator->{(string) $strategy}($states);

        return $strategy;
    }

    private function createState(): State
    {
        return new State(
            $this->clock->now(),
            $this->factors->reduce(
                Map::of('string', Measure::class),
                static function(Map $measures, Factor $factor): Map {
                    return ($measures)(
                        $factor->name(),
                        $factor->sensor()()
                    );
                }
            )
        );
    }
}
