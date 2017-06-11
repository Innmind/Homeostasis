<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis\Actuator;

use Innmind\Homeostasis\Strategy;
use Innmind\Immutable\SetInterface;

interface StrategyDeterminator
{
    /**
     * @param SetInterface<State> $states
     *
     * @throws StrategyNotDeterminable
     */
    public function __invoke(SetInterface $states): Strategy;
}
