<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis\Actuator\StrategyDeterminator;

use Innmind\Homeostasis\{
    Actuator\StrategyDeterminator,
    Exception\StrategyNotDeterminable,
    Strategy
};
use Innmind\Immutable\SetInterface;

final class Delegate implements StrategyDeterminator
{
    private $determinators;

    public function __construct(StrategyDeterminator ...$determinators)
    {
        $this->determinators = $determinators;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(SetInterface $states): Strategy
    {
        foreach ($this->determinators as $determinate) {
            try {
                return $determinate($states);
            } catch (StrategyNotDeterminable $e) {
                //pass
            }
        }

        throw new StrategyNotDeterminable;
    }
}
