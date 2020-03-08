<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis\Actuator\StrategyDeterminator;

use Innmind\Homeostasis\{
    Actuator\StrategyDeterminator,
    Math\Dataset\Augment,
    Math\Dataset\TimeSensitive,
    Strategy
};
use Innmind\Math\{
    Algebra\Integer,
    Algebra\Number,
    DefinitionSet\Range,
    Regression\LinearRegression
};
use Innmind\Immutable\Sequence;

final class CrossLane implements StrategyDeterminator
{
    private Range $bounds;
    private Augment $augment;

    public function __construct(Augment $augment)
    {
        $this->bounds = Range::inclusive(new Integer(0), new Integer(1));
        $this->augment = $augment;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(Sequence $states): Strategy
    {
        $dataset = ($this->augment)(
            (new TimeSensitive)($states)
        );
        $trend = new LinearRegression($dataset);
        $first = $trend(new Integer(0));
        $furthest = $trend(
            $dataset
                ->abscissas()
                ->get(
                    $dataset->abscissas()->dimension()->decrement()->value()
                )
        );

        return $this->strategy($first, $furthest, $trend);
    }

    private function strategy(
        Number $first,
        Number $furthest,
        LinearRegression $trend
    ): Strategy {
        if (
            !$this->bounds->contains($first) ||
            !$this->bounds->contains($furthest)
        ) {
            return $this->outside($trend);
        }

        return $this->inside($trend);
    }

    private function outside(LinearRegression $trend): Strategy
    {
        if ($trend->slope()->higherThan(new Integer(0))) {
            return Strategy::dramaticDecrease();
        }

        return Strategy::dramaticIncrease();
    }

    private function inside(LinearRegression $trend): Strategy
    {
        if ($trend->slope()->higherThan(new Integer(0))) {
            return Strategy::decrease();
        }

        return Strategy::increase();
    }
}
