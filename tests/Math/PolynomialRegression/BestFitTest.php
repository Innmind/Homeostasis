<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis\Math\PolynomialRegression;

use Innmind\Homeostasis\Math\PolynomialRegression\BestFit;
use Innmind\Math\{
    Regression\Dataset,
    Polynom\Polynom,
    Algebra\Integer
};
use PHPUnit\Framework\TestCase;

class BestFitTest extends TestCase
{
    public function testInvokation()
    {
        $fit = new BestFit(
            Dataset::fromArray([
                [0, 0],
                [0.1, 0.1],
                [0.2, 0.175],
                [0.5, 0.4],
                [0.6, 0.4],
                [0.7, 0.45],
                [0.8, 0.5],
                [0.9, 0.8],
                [1, 1],
            ])
        );

        $polynom = $fit(new Integer(1), new Integer(9));

        $this->assertInstanceOf(Polynom::class, $polynom);
        $this->assertTrue($polynom->hasDegree(1));
        $this->assertTrue($polynom->hasDegree(2));
        $this->assertTrue($polynom->hasDegree(3));
        $this->assertTrue($polynom->hasDegree(4));
        $this->assertTrue($polynom->hasDegree(5));
        $this->assertTrue($polynom->hasDegree(6));
        $this->assertFalse($polynom->hasDegree(7));
        $this->assertFalse($polynom->hasDegree(8));
        $this->assertFalse($polynom->hasDegree(9));
        $this->assertSame(-0.0012195890835040666, $polynom->intercept()->value());
        $this->assertSame(2.0996410102652, $polynom->degree(1)->coeff()->value());
        $this->assertSame(-17.27684076838, $polynom->degree(2)->coeff()->value());
        $this->assertSame(86.261146237871, $polynom->degree(3)->coeff()->value());
        $this->assertSame(-189.7736029403, $polynom->degree(4)->coeff()->value());
        $this->assertSame(184.66906744449, $polynom->degree(5)->coeff()->value());
        $this->assertSame(-64.975065630889, $polynom->degree(6)->coeff()->value());
    }

    /**
     * @expectedException Innmind\Homeostasis\Exception\BestFitNotDeterminable
     */
    public function testThrowWhenBestFitNotDeterminable()
    {
        $fit = new BestFit(
            Dataset::fromArray([
                [2, 3],
                [1, 1/3],
            ])
        );

        $fit(new Integer(5), new Integer(5));
    }
}
