<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis;

use Innmind\Homeostasis\{
    Actuator\StrategyDeterminator,
    Actuator\StrategyDeterminators,
};
use Innmind\Filesystem\Adapter;
use Innmind\TimeContinuum\{
    TimeContinuumInterface,
    ElapsedPeriod,
};
use Innmind\Immutable\SetInterface;

function bootstrap(
    SetInterface $factors,
    Actuator $actuator,
    Adapter $stateFilesystem,
    TimeContinuumInterface $clock,
    StrategyDeterminator $determinator = null
): array {
    $determinator = $determinator ?? StrategyDeterminators::default();

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
            $max = $max ?? new ElapsedPeriod(86400000); // one day
            $min = $min ?? new ElapsedPeriod(3600000); // one hour

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
        'thread_safe' => static function(Regulator $regulator): Regulator {
            return new Regulator\ThreadSafe($regulator);
        },
    ];
}