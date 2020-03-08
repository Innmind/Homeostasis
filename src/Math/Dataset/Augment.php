<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis\Math\Dataset;

use Innmind\Homeostasis\{
    Math\PolynomialRegression\BestFit,
    Exception\AugmentAtLeastByOne
};
use Innmind\Math\{
    Algebra\Integer,
    Regression\Dataset
};

final class Augment
{
    private Integer $predict;

    public function __construct(Integer $predict)
    {
        if ((new Integer(1))->higherThan($predict)) {
            throw new AugmentAtLeastByOne;
        }

        $this->predict = $predict;
    }

    public function __invoke(Dataset $dataset): Dataset
    {
        $polynom = (new BestFit($dataset))(
            new Integer(1),
            $dataset->abscissas()->dimension()->decrement()
        );

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
