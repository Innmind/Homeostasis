<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis\Actuator\StrategyDeterminator;

use Innmind\Homeostasis\{
    Actuator\StrategyDeterminator,
    Strategy,
    Math\Dataset\Augment,
    Math\Dataset\TimeSensitive,
    Exception\StrategyNotDeterminable
};
use Innmind\Math\{
    Algebra\Number,
    Algebra\Integer,
    Regression\LinearRegression,
    Regression\Dataset,
    DefinitionSet\Range
};
use Innmind\Immutable\Sequence;

final class WaterLane implements StrategyDeterminator
{
    private Range $bounds;
    private Augment $augment;
    private Strategy $increase;
    private Strategy $decrease;

    public function __construct(
        Range $bounds,
        Augment $augment,
        Strategy $increase,
        Strategy $decrease
    ) {
        $this->bounds = $bounds;
        $this->augment = $augment;
        $this->increase = $increase;
        $this->decrease = $decrease;
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

        if (
            $this->notInBounds($trend->intercept()) ||
            $this->crossLane($dataset, $trend)
        ) {
            throw new StrategyNotDeterminable;
        }

        if ($trend->slope()->higherThan(new Integer(0))) {
            return $this->decrease;
        }

        return $this->increase;
    }

    private function notInBounds(Number $number): bool
    {
        return !$this->bounds->contains($number);
    }

    private function crossLane(Dataset $dataset, LinearRegression $trend): bool
    {
        $furthest = $trend(
            $dataset
                ->abscissas()
                ->get($dataset->abscissas()->dimension()->decrement()->value())
        );

        return $this->notInBounds($furthest);
    }
}
