<?php

declare(strict_types=1);

namespace TiMMiT\PHPStan\Rules\Tests;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use TiMMiT\PHPStan\Rules\NoDateTimeModifyRule;

/**
 * @extends RuleTestCase<NoDateTimeModifyRule>
 */
class NoDateTimeModifyRuleTest extends RuleTestCase
{
    use ReportsIdentifiersPerLine;

    protected function getRule(): Rule
    {
        return new NoDateTimeModifyRule();
    }

    public function testFlagsModify(): void
    {
        $errors = $this->gatherAnalyserErrors([__DIR__ . '/Fixture/DateTimeStrictFixture.php']);

        $this->assertSame(
            [22 => ['timmit.noDateTimeModify']],
            $this->reportedIdentifiersPerLine($errors)
        );
    }
}
