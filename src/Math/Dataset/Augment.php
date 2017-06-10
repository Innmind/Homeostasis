<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis\Math\Dataset;

use Innmind\Homeostasis\Exception\AugmentAtLeastByOne;
use Innmind\Math\{
    Algebra\Integer,
    Regression\Dataset,
    Regression\PolynomialRegression
};

final class Augment
{
    private $predict;

    public function __construct(Integer $predict)
    {
        if ((new Integer(1))->higherThan($predict)) {
            throw new AugmentAtLeastByOne;
        }

        $this->predict = $predict;
    }

    public function __invoke(Dataset $dataset): Dataset
    {
        $polynom = new PolynomialRegression($dataset, new Integer(4));

        $abscissas = $dataset->abscissas();
        $dimension = $abscissas->dimension()->value();
        $last = $abscissas->get($dimension - 1);
        $delta = $last->subtract(
            $abscissas->get($dimension - 2)
        );
        $x = $last;
        $predicted = new Integer(0);

        do {
            $x = $x->add($delta);
            $estimation = $polynom($x);
            $dataset = $dataset->toArray();
            $dataset[] = [$x, $estimation];
            $dataset = Dataset::fromArray($dataset);

            $predicted = $predicted->increment();
        } while ($this->predict->higherThan($predicted));

        return $dataset;
    }
}
