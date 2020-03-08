<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis\Regulator;

use Innmind\Homeostasis\{
    Regulator,
    ActionHistory,
    StateHistory,
    Strategy,
    Action
};
use Innmind\TimeContinuum\{
    TimeContinuumInterface,
    PointInTimeInterface,
    ElapsedPeriod,
    Period\Earth\Millisecond
};
use Innmind\Math\{
    Algebra\Number\Number,
    Algebra\Integer,
    Statistics\Frequence
};
use Innmind\Immutable\{
    Stream,
    Pair
};

/**
 * Reduce the state history when everything is globally stable in order to
 * detect more easily peaks
 * If states are eratic we want a longer history as we want to know the global
 * trend
 */
final class ModulateStateHistory implements Regulator
{
    private Regulator $regulate;
    private ActionHistory $actions;
    private StateHistory $states;
    private TimeContinuumInterface $clock;
    private ElapsedPeriod $maxHistory;
    private ElapsedPeriod $minHistory;
    private Number $threshold;

    public function __construct(
        Regulator $regulator,
        ActionHistory $actions,
        StateHistory $states,
        TimeContinuumInterface $clock,
        ElapsedPeriod $maxHistory,
        ElapsedPeriod $minHistory
    ) {
        $this->regulate = $regulator;
        $this->actions = $actions;
        $this->states = $states;
        $this->clock = $clock;
        $this->maxHistory = $maxHistory;
        $this->minHistory = $minHistory;
        $this->threshold = new Number(0.75); //allow a fourth variation over time
    }

    public function __invoke(): Strategy
    {
        $strategy = ($this->regulate)();
        $this->actions->add(new Action(
            $this->clock->now(),
            $strategy
        ));

        $this->keepUp(
            $this->clock->now()->goBack(
                new Millisecond(
                    $this->maxHistory->milliseconds()
                )
            )
        );
        $this->modulate();

        return $strategy;
    }

    private function modulate(): void
    {
        $variations = $this
            ->actions
            ->all()
            ->reduce(
                new Stream(Pair::class),
                static function(Stream $variations, Action $action): Stream {
                    if ($variations->size() === 0) {
                        return $variations->add(new Pair(new Integer(0), $action));
                    }

                    return $variations->add(new Pair(
                        $action->variation($variations->last()->value()),
                        $action
                    ));
                }
            )
            ->reduce(
                new Stream(Integer::class),
                static function(Stream $variations, Pair $action): Stream {
                    return $variations->add(
                        $action->key()
                    );
                }
            );
        $frequence = new Frequence(...$variations);

        if ($frequence(new Integer(0))->higherThan($this->threshold)) {
            $this->keepUp(
                $this->clock->now()->goBack(
                    new Millisecond(
                        $this->minHistory->milliseconds()
                    )
                )
            );
        }
    }

    private function keepUp(PointInTimeInterface $time): void
    {
        $this->actions->keepUp($time);
        $this->states->keepUp($time);
    }
}
