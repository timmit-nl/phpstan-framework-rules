<?php

/**
 * Fixture for YodaComparisonRuleTest, analysed by PHPStan and never executed.
 */

declare(strict_types=1);

namespace Timmit\Phpstan\Rules\Tests\Fixture;

/**
 * Exercises Yoda comparisons, including signed numeric literals.
 */
class YodaComparisonFixture
{
    public function nonYodaInt(int $value): bool
    {
        return $value === 1;
    }

    public function yodaInt(int $value): bool
    {
        return 1 === $value;
    }

    public function nonYodaNegative(int $value): bool
    {
        return $value === -1;
    }

    public function yodaNegative(int $value): bool
    {
        return -1 === $value;
    }

    public function nonYodaPositiveUnary(int $value): bool
    {
        return $value === +1;
    }

    public function nonYodaNegativeFloat(float $value): bool
    {
        return $value === -1.5;
    }

    public function yodaNegativeFloat(float $value): bool
    {
        return -1.5 === $value;
    }

    public function twoVariables(int $left, int $right): bool
    {
        return $left === $right;
    }
}
