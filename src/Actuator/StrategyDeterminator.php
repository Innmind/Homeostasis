<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis\Actuator;

use Innmind\Homeostasis\{
    Strategy,
    State,
    Exception\StrategyNotDeterminable,
};
use Innmind\Immutable\Sequence;

interface StrategyDeterminator
{
    /**
     * @param Sequence<State> $states
     *
     * @throws StrategyNotDeterminable
     */
    public function __invoke(Sequence $states): Strategy;
}
