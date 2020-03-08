<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis;

final class Strategy
{
    private const DRAMATIC_DECREASE = 'dramaticDecrease';
    private const DECREASE = 'decrease';
    private const HOLD_STEADY = 'holdSteady';
    private const INCREASE = 'increase';
    private const DRAMATIC_INCREASE = 'dramaticIncrease';

    private static ?self $dramaticDecrease = null;
    private static ?self $decrease = null;
    private static ?self $holdSteady = null;
    private static ?self $increase = null;
    private static ?self $dramaticIncrease = null;

    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function dramaticDecrease(): self
    {
        return self::$dramaticDecrease ?? self::$dramaticDecrease = new self(
            self::DRAMATIC_DECREASE
        );
    }

    public static function decrease(): self
    {
        return self::$decrease ?? self::$decrease = new self(
            self::DECREASE
        );
    }

    public static function holdSteady(): self
    {
        return self::$holdSteady ?? self::$holdSteady = new self(
            self::HOLD_STEADY
        );
    }

    public static function increase(): self
    {
        return self::$increase ?? self::$increase = new self(
            self::INCREASE
        );
    }

    public static function dramaticIncrease(): self
    {
        return self::$dramaticIncrease ?? self::$dramaticIncrease = new self(
            self::DRAMATIC_INCREASE
        );
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
