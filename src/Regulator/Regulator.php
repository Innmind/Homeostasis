<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis\Regulator;

use Innmind\Homeostasis\{
    Regulator as RegulatorInterface,
    Strategy,
    StateRepository,
    Factor,
    State,
    State\Identity\PointInTime,
    Sensor\Measure,
    Actuator,
    Actuator\StrategyDeterminator,
    Exception\InvalidFactors
};
use Innmind\TimeContinuum\TimeContinuumInterface;
use Innmind\Immutable\{
    SetInterface,
    Map
};

final class Regulator implements RegulatorInterface
{
    private $factors;
    private $repository;
    private $clock;
    private $strategyDeterminator;
    private $actuator;

    public function __construct(
        SetInterface $factors,
        StateRepository $repository,
        TimeContinuumInterface $clock,
        StrategyDeterminator $strategyDeterminator,
        Actuator $actuator
    ) {
        if ((string) $factors->type() !== Factor::class) {
            throw new InvalidFactors;
        }

        $this->factors = $factors;
        $this->repository = $repository;
        $this->clock = $clock;
        $this->strategyDeterminator = $strategyDeterminator;
        $this->actuator = $actuator;
    }

    public function __invoke(): Strategy
    {
        $states = $this
            ->repository
            ->add($this->createState())
            ->all();

        $strategy = ($this->strategyDeterminator)($states);

        $this->actuator->{(string) $strategy}($states);

        return $strategy;
    }

    private function createState(): State
    {
        $now = $this->clock->now();

        return new State(
            new PointInTime($now),
            $now,
            $this->factors->reduce(
                new Map('string', Measure::class),
                static function(Map $measures, Factor $factor): Map {
                    return $measures->put(
                        $factor->name(),
                        $factor->sensor()()
                    );
                }
            )
        );
    }
}
