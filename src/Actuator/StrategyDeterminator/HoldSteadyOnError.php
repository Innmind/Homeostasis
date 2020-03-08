<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis\Actuator\StrategyDeterminator;

use Innmind\Homeostasis\{
    Actuator\StrategyDeterminator,
    Exception\Exception,
    Strategy
};
use Innmind\Immutable\StreamInterface;

final class HoldSteadyOnError implements StrategyDeterminator
{
    private StrategyDeterminator $determinate;

    public function __construct(StrategyDeterminator $determinator)
    {
        $this->determinate = $determinator;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(StreamInterface $states): Strategy
    {
        try {
            return ($this->determinate)($states);
        } catch (Exception $e) {
            return Strategy::holdSteady();
        }
    }
}
