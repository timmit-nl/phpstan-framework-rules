<?php

/**
 * Fixture for DateTimeFormatIntComparisonRuleTest, analysed by PHPStan and never executed.
 */

declare(strict_types=1);

namespace TiMMiT\PHPStan\Rules\Tests\Fixture;

use DateTime;

/**
 * Exercises DateTime format comparison patterns.
 */
class DateTimeFormatComparisonFixture
{
    public function stringLiteralDayComparison(DateTime $reference): bool
    {
        return '01' === $reference->format('d');
    }

    public function intCastDayComparison(DateTime $reference): bool
    {
        return 1 === (int)$reference->format('d');
    }

    public function stringLiteralMonthComparison(DateTime $reference): bool
    {
        return '12' !== $reference->format('m');
    }

    public function fullDateStringComparison(DateTime $reference, string $begindatum): bool
    {
        return $reference->format('Y-m-d') === $begindatum;
    }

    public function twoFormatCallsComparison(DateTime $left, DateTime $right): bool
    {
        return $left->format('d') === $right->format('d');
    }
}
