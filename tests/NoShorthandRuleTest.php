<?php

declare(strict_types=1);

namespace Timmit\Phpstan\Rules\Tests;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Timmit\Phpstan\Rules\NoShorthandRule;

/**
 * @extends RuleTestCase<NoShorthandRule>
 */
class NoShorthandRuleTest extends RuleTestCase
{
    use ReportsIdentifiersPerLine;

    protected function getRule(): Rule
    {
        return new NoShorthandRule();
    }

    public function testFlagsShorthandAndAllowsNullCoalescing(): void
    {
        $errors = $this->gatherAnalyserErrors([__DIR__ . '/Fixture/ShorthandFixture.php']);

        $this->assertSame(
            [
                34 => ['timmit.noNullCoalesceAssign'],
                46 => ['timmit.noTernary'],
                51 => ['timmit.noTernary'],
                56 => ['timmit.noTernary', 'timmit.noTernary'],
                61 => ['timmit.noNullsafeOperator'],
                66 => ['timmit.noNullsafeOperator'],
                71 => ['timmit.noArrowFunction'],
                76 => ['timmit.noMatch'],
            ],
            $this->reportedIdentifiersPerLine($errors)
        );
    }
}
