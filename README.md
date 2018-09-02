# Homeostasis

| `master` | `develop` |
|----------|-----------|
| [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Innmind/Homeostasis/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Innmind/Homeostasis/?branch=master) | [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Innmind/Homeostasis/badges/quality-score.png?b=develop)](https://scrutinizer-ci.com/g/Innmind/Homeostasis/?branch=develop) |
| [![Code Coverage](https://scrutinizer-ci.com/g/Innmind/Homeostasis/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Innmind/Homeostasis/?branch=master) | [![Code Coverage](https://scrutinizer-ci.com/g/Innmind/Homeostasis/badges/coverage.png?b=develop)](https://scrutinizer-ci.com/g/Innmind/Homeostasis/?branch=develop) |
| [![Build Status](https://scrutinizer-ci.com/g/Innmind/Homeostasis/badges/build.png?b=master)](https://scrutinizer-ci.com/g/Innmind/Homeostasis/build-status/master) | [![Build Status](https://scrutinizer-ci.com/g/Innmind/Homeostasis/badges/build.png?b=develop)](https://scrutinizer-ci.com/g/Innmind/Homeostasis/build-status/develop) |

This lib is a mechanism to collect indicators from various sensors of your app (cpu usage, errors in logs, ...) and determine what action to take depending on the health of the system. For example if the app takes too much cpu for too long you should reduce the number of processes handled by the server.

In essence the process is always like this: `collect sensor values => determine strategy => call actuators`.

**Note**: no actuators is implemented in this library because these are application specific so it's up to you to know how to regulate your app.

## Usage

```php
use function Innmind\Homeostasis\bootstrap;
use Innmind\Homeostasis\{
    Factor,
    Factor\Cpu,
    Factor\Log,
    Sensor\Measure\Weight
};
use Innmind\Filesystem\Adapter;
use Innmind\Immutable\Set;
use Innmind\Server\Status\ServerFactory;
use Innmind\Math\{
    Polynom\Polynom,
    Algebra\Integer
    Algebra\Number\Number
};
use Psr\Log\LogLevel;
use Innmind\TimeContinuum\TimeContinuum\Earth;
use Innmind\Filesystem\Adapter\FilesystemAdapter;

$clock = new Earth;
$homeostasis = bootstrap(
    Set::of(
        Factor::class,
        new Cpu(
            $clock,
            (new ServerFactory($clock))->make(),
            new Weight(new Number(0.5)),
            (new Polynom)->withDegree(new Integer(1), new Integer(1))
        ),
        new Log(
            $clock,
            new Synchronous(new Symfony($clock)),
            new FilesystemAdapter('var/logs'),
            new Weight(new Number(0.5)),
            (new Polynom)->withDegree(new Integer(1), new Integer(1)),
            static function(Log $line): bool {
                return $line->attributes()->contains('level') &&
                    $line->attributes()->get('level')->value() === LogLevel:CRITICAL;
            },
            'symfony'
        )
    ),
    /*you need to implement the Actuator interface*/,
    new FilesystemAdapter('some/path/to/store/states'),
    $clock
);

$modulateStateHistory = $homeostasis['modulate_state_history'](
    new FilesystemAdapter('some/path/to/store/actions')
);

$regulate = $homeostasis['thread_safe'](
    $modulateStateHistory(
        $homeostasis['regulator']
    )
);

$regulate();
```

Above we defined a regulator that collects values from the cpu usage and the errors from the symfony logs. Each sensor is given the same importance/weight.

A sensor must return a value between `0` and `1`, `0` means there is not enough activity and `1` means there's too much. So the obvious goal is that the overall value tends toward `0.5`. But as for each sensor the way to calculate this value is different and non linear, you an specify a polynom to modulate this. In the example above we specified a linear polynom for both cpu and logs but you should change those as _not enough errors_ in the logs makes no sense; polynoms should look something like this:

```
1 |                      /
  |                     /
  |                    /
  |     ______________/      Means that between 20% and 80% of cpu usage you're good
  |    /                     otherwise there's not enough or too much activity
  |   /
  |  /
  |_/_ _ _ _ _ _ _ _ _ _ _
 0           CPU          1
```
```
1 |  /--------------------
  | /
  |/
  |
  |                          As soon there are errors in the logs you're in alert
  |
  |
  |_ _ _ _ _ _ _ _ _ _ _ _
 0           LOGS         1
```

Finally, the `ModulateStateHistory` wrap is here to erase part of the history otherwise the system would have a hard time to find the tendency in the activity.
