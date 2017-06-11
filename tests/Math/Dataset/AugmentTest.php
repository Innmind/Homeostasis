<?php
declare(strict_types = 1);

namespace Tests\Innmind\Homeostasis\Math\Dataset;

use Innmind\Homeostasis\Math\Dataset\Augment;
use Innmind\Math\{
    Algebra\Integer,
    Regression\Dataset
};
use PHPUnit\Framework\TestCase;

class AugmentTest extends TestCase
{
    /**
     * @expectedException Innmind\Homeostasis\Exception\AugmentAtLeastByOne
     */
    public function testThrowWhenAugmentLessThanOne()
    {
        new Augment(new Integer(0));
    }

    public function testInvokation()
    {
        $augment = new Augment(new Integer(2));

        $augmented = $augment(
            $dataset = Dataset::fromArray($data = [
                [0, 0],
                [1, 1],
                [2, 2],
                [3, 4.5],
                [4, 8],
                [5, 12.5],
                [6, 12.5],
                [7, 8],
            ])
        );

        $this->assertInstanceOf(Dataset::class, $augmented);
        $this->assertNotSame($dataset, $augmented);
        $this->assertSame($data, $dataset->toArray());
        $data[] = [8, 58.00000159408228];
        $data[] = [9, 401.99997883156175];
        $this->assertSame($data, $augmented->toArray());
    }
}
