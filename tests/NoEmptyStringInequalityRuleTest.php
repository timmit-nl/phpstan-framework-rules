<?php

declare(strict_types=1);

namespace Timmit\Phpstan\Rules\Tests;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Timmit\Phpstan\Rules\NoEmptyStringInequalityRule;

/**
 * @extends RuleTestCase<NoEmptyStringInequalityRule>
 */
class NoEmptyStringInequalityRuleTest extends RuleTestCase
{
    use ReportsIdentifiersPerLine;

    protected function getRule(): Rule
    {
        return new NoEmptyStringInequalityRule();
    }

    public function testFlagsStringInequalityAgainstEmptyString(): void
    {
        $errors = $this->gatherAnalyserErrors([__DIR__ . '/Fixture/EmptyStringInequalityFixture.php']);

        $this->assertSame(
            [
                20 => ['timmit.noEmptyStringInequality'],
                25 => ['timmit.noEmptyStringInequality'],
            ],
            $this->reportedIdentifiersPerLine($errors)
        );
    }
}
