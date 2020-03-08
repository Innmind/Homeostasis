<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis\Actuator\StrategyDeterminator;

use Innmind\Homeostasis\{
    Actuator\StrategyDeterminator,
    Exception\StrategyNotDeterminable,
    Strategy
};
use Innmind\Immutable\Sequence;

final class Delegate implements StrategyDeterminator
{
    /** @var list<StrategyDeterminator> */
    private array $determinators;

    public function __construct(StrategyDeterminator ...$determinators)
    {
        $this->determinators = $determinators;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(Sequence $states): Strategy
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
