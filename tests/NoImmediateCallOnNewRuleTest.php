<?php

declare(strict_types=1);

namespace Timmit\Phpstan\Rules\Tests;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Timmit\Phpstan\Rules\NoImmediateCallOnNewRule;

/**
 * @extends RuleTestCase<NoImmediateCallOnNewRule>
 */
class NoImmediateCallOnNewRuleTest extends RuleTestCase
{
    use ReportsIdentifiersPerLine;

    protected function getRule(): Rule
    {
        return new NoImmediateCallOnNewRule();
    }

    public function testFlagsDirectCallOnNewAndAllowsPlainInstantiation(): void
    {
        $errors = $this->gatherAnalyserErrors([__DIR__ . '/Fixture/ImmediateCallOnNewFixture.php']);

        $this->assertSame(
            [
                22 => ['timmit.noImmediateCallOnNew'],
                34 => ['timmit.noImmediateCallOnNew'],
            ],
            $this->reportedIdentifiersPerLine($errors)
        );
    }
}
