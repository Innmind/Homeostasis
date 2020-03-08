<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis\Regulator;

use Innmind\Homeostasis\{
    Regulator,
    ActionHistory,
    StateHistory,
    Strategy,
    Action,
};
use Innmind\TimeContinuum\{
    Clock,
    PointInTime,
    ElapsedPeriod,
    Earth\Period\Millisecond,
};
use Innmind\Math\{
    Algebra\Number\Number,
    Algebra\Integer,
    Statistics\Frequence,
};
use Innmind\Immutable\{
    Sequence,
    Pair,
};
use function Innmind\Immutable\unwrap;

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
    private Clock $clock;
    private ElapsedPeriod $maxHistory;
    private ElapsedPeriod $minHistory;
    private Number $threshold;

    public function __construct(
        Regulator $regulator,
        ActionHistory $actions,
        StateHistory $states,
        Clock $clock,
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
            $strategy,
        ));

        $this->keepUp(
            $this->clock->now()->goBack(
                new Millisecond(
                    $this->maxHistory->milliseconds(),
                ),
            ),
        );
        $this->modulate();

        return $strategy;
    }

    private function modulate(): void
    {
        /** @var Sequence<Pair<Integer, Action>> */
        $variationPerAction = Sequence::of(Pair::class);
        $variations = $this
            ->actions
            ->all()
            ->reduce(
                $variationPerAction,
                static function(Sequence $variations, Action $action): Sequence {
                    /** @var Sequence<Pair<Integer, Action>> */
                    $variations = $variations;

                    if ($variations->empty()) {
                        return ($variations)(new Pair(new Integer(0), $action));
                    }

                    return ($variations)(new Pair(
                        $action->variation($variations->last()->value()),
                        $action,
                    ));
                },
            )
            ->mapTo(
                Integer::class,
                static fn(Pair $action): Integer => $action->key(),
            );

        $frequence = new Frequence(...unwrap($variations));

        if ($frequence(new Integer(0))->higherThan($this->threshold)) {
            $this->keepUp(
                $this->clock->now()->goBack(
                    new Millisecond(
                        $this->minHistory->milliseconds(),
                    ),
                ),
            );
        }
    }

    private function keepUp(PointInTime $time): void
    {
        $this->actions->keepUp($time);
        $this->states->keepUp($time);
    }
}
