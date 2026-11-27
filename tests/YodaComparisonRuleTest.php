<?php

declare(strict_types=1);

namespace TiMMiT\PHPStan\Rules\Tests;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use TiMMiT\PHPStan\Rules\YodaComparisonRule;

/**
 * @extends RuleTestCase<YodaComparisonRule>
 */
class YodaComparisonRuleTest extends RuleTestCase
{
    use ReportsIdentifiersPerLine;

    protected function getRule(): Rule
    {
        return new YodaComparisonRule();
    }

    public function testFlagsNonYodaComparisonsIncludingSignedLiterals(): void
    {
        $errors = $this->gatherAnalyserErrors([__DIR__ . '/Fixture/YodaComparisonFixture.php']);

        $this->assertSame(
            [
                18 => ['timmit.yodaComparison'],
                28 => ['timmit.yodaComparison'],
                38 => ['timmit.yodaComparison'],
                43 => ['timmit.yodaComparison'],
            ],
            $this->reportedIdentifiersPerLine($errors)
        );
    }
}
