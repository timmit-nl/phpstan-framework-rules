<?php

declare(strict_types=1);

namespace Timmit\Phpstan\Rules\Tests;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Timmit\Phpstan\Rules\NoGetBaseFieldOfJugglingRule;

/**
 * @extends RuleTestCase<NoGetBaseFieldOfJugglingRule>
 */
class NoGetBaseFieldOfJugglingRuleTest extends RuleTestCase
{
    use ReportsIdentifiersPerLine;

    protected function getRule(): Rule
    {
        return new NoGetBaseFieldOfJugglingRule();
    }

    public function testFlagsGetBaseFieldOfGroupId(): void
    {
        $errors = $this->gatherAnalyserErrors([__DIR__ . '/Fixture/GetBaseFieldOfGroupIdFixture.php']);

        $this->assertSame(
            [
                18 => ['timmit.noGetBaseFieldOfGroupId'],
                23 => ['timmit.noGetBaseFieldOfGroupId'],
                38 => ['timmit.noGetBaseFieldOfGroupId'],
            ],
            $this->reportedIdentifiersPerLine($errors)
        );
    }
}
