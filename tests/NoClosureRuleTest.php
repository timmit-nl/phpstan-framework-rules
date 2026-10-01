<?php

declare(strict_types=1);

namespace Timmit\Phpstan\Rules\Tests;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Timmit\Phpstan\Rules\NoClosureRule;

/**
 * @extends RuleTestCase<NoClosureRule>
 */
class NoClosureRuleTest extends RuleTestCase
{
    use ReportsIdentifiersPerLine;

    protected function getRule(): Rule
    {
        return new NoClosureRule();
    }

    public function testFlagsClosuresAndAllowsFirstClassCallables(): void
    {
        $errors = $this->gatherAnalyserErrors([__DIR__ . '/Fixture/ClosureFixture.php']);

        $this->assertSame(
            [
                18 => ['timmit.noClosure'],
            ],
            $this->reportedIdentifiersPerLine($errors)
        );
    }
}
