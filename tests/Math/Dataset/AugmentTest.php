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
                [8, 4.5],
                [9, 2],
                [10, 1],
                [11, 0],
            ])
        );

        $this->assertInstanceOf(Dataset::class, $augmented);
        $this->assertNotSame($dataset, $augmented);
        $this->assertSame($data, $dataset->toArray());
        $data[] = [12, 7.159090909099177];
        $data[] = [13, 22.72552447554358];
        $this->assertSame($data, $augmented->toArray());
    }
}
