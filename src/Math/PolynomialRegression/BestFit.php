<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis\Math\PolynomialRegression;

use function Innmind\Math\add;
use Innmind\Math\{
    Regression\Dataset,
    Regression\PolynomialRegression,
    Algebra\Integer,
    Polynom\Polynom
};

final class BestFit
{
    private $dataset;

    public function __construct(Dataset $dataset)
    {
        $this->dataset = $dataset;
    }

    public function __invoke(Integer $a, Integer $b): Polynom
    {
        do {
            $regressed = new PolynomialRegression($this->dataset, $a);

            if (!isset($bestFit)) {
                $bestFit = $regressed;
            }

            if ($bestFit->rootMeanSquareDeviation()->higherThan($regressed->rootMeanSquareDeviation())) {
                $bestFit = $regressed;
            }

            $a = new Integer(add($a, 1)->value());
        } while ($b->higherThan($a) || $b->equals($a));

        return $bestFit->polynom();
    }
}