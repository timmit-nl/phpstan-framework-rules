<?php

declare(strict_types=1);

namespace Timmit\Phpstan\Rules\Tests;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Timmit\Phpstan\Rules\NoLooseDateFunctionsRule;

/**
 * @extends RuleTestCase<NoLooseDateFunctionsRule>
 */
class NoLooseDateFunctionsRuleTest extends RuleTestCase
{
    use ReportsIdentifiersPerLine;

    protected function getRule(): Rule
    {
        return new NoLooseDateFunctionsRule();
    }

    public function testFlagsLooseDateFunctions(): void
    {
        $errors = $this->gatherAnalyserErrors([__DIR__ . '/Fixture/DateTimeStrictFixture.php']);

        $this->assertSame(
            [57 => ['timmit.noLooseDateFunction.strtotime']],
            $this->reportedIdentifiersPerLine($errors)
        );
    }
}
