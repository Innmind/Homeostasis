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
    /** @var Set<Factor> */
    private Set $factors;
    private StateHistory $history;
    private Clock $clock;
    private StrategyDeterminator $strategyDeterminator;
    private Actuator $actuator;

    /**
     * @param Set<Factor> $factors
     */
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
        $this->history->add($this->createState());
        $states = $this->history->all();

        $strategy = ($this->strategyDeterminator)($states);

        $this->actuator->{(string) $strategy}($states);

        return $strategy;
    }

    private function createState(): State
    {
        /** @var Map<string, Measure> */
        $measures = $this->factors->toMapOf(
            'string',
            Measure::class,
            static function(Factor $factor): \Generator {
                yield $factor->name() => $factor->sensor()();
            },
        );

        return new State($this->clock->now(), $measures);
    }
}
