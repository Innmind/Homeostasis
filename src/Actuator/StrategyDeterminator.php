<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis\Actuator;

use Innmind\Homeostasis\Strategy;
use Innmind\Immutable\StreamInterface;

interface StrategyDeterminator
{
    /**
     * @param StreamInterface<State> $states
     *
     * @throws StrategyNotDeterminable
     */
    public function __invoke(StreamInterface $states): Strategy;
}
