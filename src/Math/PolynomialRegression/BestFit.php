<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis\Math\PolynomialRegression;

use Innmind\Homeostasis\Exception\BestFitNotDeterminable;
use Innmind\Math\{
    Regression\Dataset,
    Regression\PolynomialRegression,
    Algebra\Integer,
    Polynom\Polynom,
    Exception\Exception
};

final class BestFit
{
    private Dataset $dataset;

    public function __construct(Dataset $dataset)
    {
        $this->dataset = $dataset;
    }

    public function __invoke(Integer $a, Integer $b): Polynom
    {
        do {
            try {
                $regressed = new PolynomialRegression($this->dataset, $a);

                if (!isset($bestFit)) {
                    $bestFit = $regressed;
                }

                if ($bestFit->rootMeanSquareDeviation()->higherThan($regressed->rootMeanSquareDeviation())) {
                    $bestFit = $regressed;
                }
            } catch (Exception $e) {
                //attempt higher degree
            }

            $a = $a->increment();
        } while ($b->higherThan($a) || $b->equals($a));

        if (!isset($bestFit)) {
            throw new BestFitNotDeterminable;
        }

        return $bestFit->polynom();
    }
}
