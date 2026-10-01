<?php

declare(strict_types=1);

namespace Timmit\Phpstan\Rules\Tests;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Timmit\Phpstan\Rules\NoEmptyCoalesceStringComparisonRule;

/**
 * @extends RuleTestCase<NoEmptyCoalesceStringComparisonRule>
 */
class NoEmptyCoalesceStringComparisonRuleTest extends RuleTestCase
{
    use ReportsIdentifiersPerLine;

    protected function getRule(): Rule
    {
        return new NoEmptyCoalesceStringComparisonRule();
    }

    public function testFlagsEmptyCoalesceStringComparisons(): void
    {
        $errors = $this->gatherAnalyserErrors([__DIR__ . '/Fixture/EmptyCoalesceStringComparisonFixture.php']);

        $this->assertSame(
            [
                24 => ['timmit.noEmptyCoalesceStringComparison'],
                32 => ['timmit.noEmptyCoalesceStringComparison'],
                40 => ['timmit.noEmptyCoalesceStringComparison'],
                48 => ['timmit.noEmptyCoalesceStringComparison'],
            ],
            $this->reportedIdentifiersPerLine($errors)
        );
    }
}
