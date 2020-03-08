<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis;

use Innmind\Homeostasis\{
    Actuator\StrategyDeterminator,
    Actuator\StrategyDeterminators,
};
use Innmind\Filesystem\Adapter;
use Innmind\TimeContinuum\{
    Clock,
    ElapsedPeriod,
    Earth,
};
use Innmind\Immutable\Set;

/**
 * @param Set<Factor> $factors
 *
 * @return array{regulator: Regulator, modulate_state_history: callable(Adapter, ElapsedPeriod = null, ElapsedPeriod = null): (callable(Regulator): Regulator)}
 */
function bootstrap(
    Set $factors,
    Actuator $actuator,
    Adapter $stateFilesystem,
    Clock $clock,
    StrategyDeterminator $determinator = null
): array {
    $determinator ??= StrategyDeterminators::default();

    return [
        'regulator' => new Regulator\Regulator(
            $factors,
            $stateHistory = new StateHistory\Filesystem(
                $stateFilesystem,
                $clock
            ),
            $clock,
            $determinator,
            $actuator
        ),
        'modulate_state_history' => static function(Adapter $filesystem, ElapsedPeriod $max = null, ElapsedPeriod $min = null) use ($clock, $stateHistory): callable {
            $max ??= new Earth\ElapsedPeriod(86400000); // one day
            $min ??= new Earth\ElapsedPeriod(3600000); // one hour

            return static function(Regulator $regulator) use ($clock, $stateHistory, $filesystem, $max, $min): Regulator {
                return new Regulator\ModulateStateHistory(
                    $regulator,
                    new ActionHistory\Filesystem(
                        $filesystem,
                        $clock
                    ),
                    $stateHistory,
                    $clock,
                    $max,
                    $min
                );
            };
        },
    ];
}
