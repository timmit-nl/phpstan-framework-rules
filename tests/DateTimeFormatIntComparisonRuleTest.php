<?php

/**
 * PHPUnit tests for DateTimeFormatIntComparisonRule.
 */

declare(strict_types=1);

namespace TiMMiT\PHPStan\Rules\Tests;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use TiMMiT\PHPStan\Rules\DateTimeFormatIntComparisonRule;

/**
 * @extends RuleTestCase<DateTimeFormatIntComparisonRule>
 */
class DateTimeFormatIntComparisonRuleTest extends RuleTestCase
{
    use ReportsIdentifiersPerLine;

    protected function getRule(): Rule
    {
        return new DateTimeFormatIntComparisonRule();
    }

    public function testFlagsStringLiteralFormatComparisons(): void
    {
        $errors = $this->gatherAnalyserErrors([__DIR__ . '/Fixture/DateTimeFormatComparisonFixture.php']);

        $this->assertSame(
            [
                20 => ['timmit.dateTimeFormatIntComparison'],
                30 => ['timmit.dateTimeFormatIntComparison'],
            ],
            $this->reportedIdentifiersPerLine($errors)
        );
    }
}
